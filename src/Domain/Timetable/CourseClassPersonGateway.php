<?php

namespace Gibbon\Domain\Timetable;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Contracts\Database\Result;
use Gibbon\Contracts\Services\Session;
use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryableGateway;

/**
 * @version v25
 * @since   v25
 */
class CourseClassPersonGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'gibbonCourseClassPerson';
    private static $primaryKey = 'gibbonCourseClassPersonID';
    private static $searchableColumns = [];

    /**
     * Session instance
     *
     * @var Session
     */
    private $session;

    public function __construct(
        Connection $db,
        Session $session
    )
    {
        parent::__construct($db);
        $this->session = $session;
    }

    /**
     * Select students for crowd assessment.
     *
     * @param string $role
     * @param int    $gibbonCourseClassID
     * @param string $homeworkCrowdAssessOtherTeachersRead
     * @param string $homeworkCrowdAssessOtherParentsRead
     * @param string $homeworkCrowdAssessSubmitterParentsRead
     * @param string $homeworkCrowdAssessClassmatesParentsRead
     * @param string $homeworkCrowdAssessOtherStudentsRead
     * @param string $homeworkCrowdAssessClassmatesRead
     * @param string $extraWhereClause
     *
     * @return Result
     *
     * @throws \Exception If unable to resolve a proper query for the role and settings.
     */
    public function selectCrowdAssessStudents(
        string $role,
        $gibbonCourseClassID,
        $homeworkCrowdAssessOtherTeachersRead,
        $homeworkCrowdAssessOtherParentsRead,
        $homeworkCrowdAssessSubmitterParentsRead,
        $homeworkCrowdAssessClassmatesParentsRead,
        $homeworkCrowdAssessOtherStudentsRead,
        $homeworkCrowdAssessClassmatesRead,
        string $extraWhereClause = ''
    ): Result {
        $session = $this->session;
        $connection2 = $this->db()->getConnection();
        $data = null;
        $sqlList = null;

        // Fetch and display assessible submissions
        $sqlList = '';
        if (($role == 'Teacher' and $homeworkCrowdAssessOtherTeachersRead == 'Y') or ($role == 'Teacher - In Class')) {
            // Get All students in class
            $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
            $sqlList = "SELECT * FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonPerson.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID) WHERE gibbonCourseClassID=:gibbonCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $extraWhereClause ORDER BY surname, preferredName";
        } elseif ($role == 'Parent' and $homeworkCrowdAssessOtherParentsRead == 'Y') {
            // Get all students in class
            $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
            $sqlList = "SELECT * FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonPerson.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID) WHERE gibbonCourseClassID=:gibbonCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $extraWhereClause ORDER BY surname, preferredName";
        } elseif ($role == 'Parent - Child In Class') {
            // Get array of children
            $count = 0;
            $children = array();
            $dataParent = array('gibbonPersonID' => $session->get('gibbonPersonID'));
            $sqlParent = "SELECT * FROM gibbonFamilyAdult WHERE gibbonPersonID=:gibbonPersonID AND childDataAccess='Y'";
            $resultParent = $connection2->prepare($sqlParent);
            $resultParent->execute($dataParent);
            if ($resultParent->rowCount() > 0) {
                // Get child list for family
                while ($rowParent = $resultParent->fetch()) {
                    $dataChild = array('gibbonFamilyID' => $rowParent['gibbonFamilyID']);
                    $sqlChild = "SELECT gibbonPerson.gibbonPersonID, image_240, surname, preferredName, gibbonYearGroup.nameShort AS yearGroup, gibbonFormGroup.nameShort AS formGroup FROM gibbonFamilyChild JOIN gibbonPerson ON (gibbonFamilyChild.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) JOIN gibbonFormGroup ON (gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID) WHERE gibbonFamilyID=:gibbonFamilyID AND gibbonPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                    while ($rowChild = $resultChild->fetch()) {
                        $children[$count] = $rowChild['gibbonPersonID'];
                        ++$count;
                    }
                }
            }

            if ($homeworkCrowdAssessSubmitterParentsRead == 'Y' and $homeworkCrowdAssessClassmatesParentsRead == 'Y') {
                // Get all students in class
                $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
                $sqlList = "SELECT * FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonPerson.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID) WHERE gibbonCourseClassID=:gibbonCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $extraWhereClause ORDER BY surname, preferredName";
            } elseif ($homeworkCrowdAssessSubmitterParentsRead == 'Y') {
                // Get only parent's children
                $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
                $sqlListWhere = 'AND (';
                for ($i = 0; $i < $count; ++$i) {
                    $data[$children[$i]] = $children[$i];
                    $sqlListWhere .= 'gibbonCourseClassPerson.gibbonPersonID=:'.$children[$i].' OR ';
                }
                if ($sqlListWhere == 'AND (') {
                    $sqlListWhere = '';
                } else {
                    $sqlListWhere = substr($sqlListWhere, 0, -4).')';
                }
                $sqlList = "SELECT * FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonPerson.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID) WHERE gibbonCourseClassID=:gibbonCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $sqlListWhere $extraWhereClause ORDER BY surname, preferredName";
            } elseif ($homeworkCrowdAssessClassmatesParentsRead == 'Y') {
                // Get all children except parent's children
                $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
                $sqlListWhere = '';
                for ($i = 0; $i < $count; ++$i) {
                    $data[$children[$i]] = $children[$i];
                    $sqlListWhere .= ' AND NOT gibbonCourseClassPerson.gibbonPersonID=:'.$children[$i];
                }
                $sqlList = "SELECT * FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonPerson.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID) WHERE gibbonCourseClassID=:gibbonCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $sqlListWhere $extraWhereClause ORDER BY surname, preferredName";
            }
        } elseif (($role == 'Student' and $homeworkCrowdAssessOtherStudentsRead == 'Y') or ($role == 'Student - In Class' and $homeworkCrowdAssessClassmatesRead == 'Y')) {
            $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
            $sqlList = "SELECT * FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonPerson.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID) WHERE gibbonCourseClassID=:gibbonCourseClassID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $extraWhereClause ORDER BY surname, preferredName";
        } elseif ($role == 'Student - In Class') {
            $data = array('gibbonCourseClassID' => $gibbonCourseClassID,'gibbonPersonID' => $session->get('gibbonPersonID'));
            $sqlList = "SELECT * FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonPerson.gibbonPersonID=gibbonCourseClassPerson.gibbonPersonID) WHERE gibbonCourseClassID=:gibbonCourseClassID AND gibbonCourseClassPerson.gibbonPersonID=:gibbonPersonID AND role='Student' AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') $extraWhereClause ORDER BY surname, preferredName";
        } else {
            throw new \Exception(__('The selected record does not exist, or you do not have access to it.'));
        }

        return $this->db()->select($sqlList, $data);
    }

    /**
     * Get the role of crowd assessment.
     *
     * @param int $gibbonCourseClassID
     *
     * @return string  The string representation of the role.
     */
    public function getCrowdAssessRole($gibbonCourseClassID): string
    {
        $session = $this->session;
        $connection2 = $this->db()->getConnection();

        $role = '';
        if (getRoleCategory($session->get('gibbonRoleIDCurrent'), $connection2) == 'Parent') {
            $role = 'Parent';
            $childInClass = false;

            // Is child of this perosn in this class?
            $count = 0;
            $children = array();

            $dataParent = array('gibbonPersonID' => $session->get('gibbonPersonID'));
            $sqlParent = "SELECT * FROM gibbonFamilyAdult WHERE gibbonPersonID=:gibbonPersonID AND childDataAccess='Y'";
            $resultParent = $connection2->prepare($sqlParent);
            $resultParent->execute($dataParent);

            if ($resultParent->rowCount() > 0) {
                // Get child list for family
                while ($rowParent = $resultParent->fetch()) {

                        $dataChild = array('gibbonFamilyID' => $rowParent['gibbonFamilyID']);
                        $sqlChild = "SELECT gibbonPerson.gibbonPersonID, image_240, surname, preferredName, gibbonYearGroup.nameShort AS yearGroup, gibbonFormGroup.nameShort AS formGroup FROM gibbonFamilyChild JOIN gibbonPerson ON (gibbonFamilyChild.gibbonPersonID=gibbonPerson.gibbonPersonID) JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID) JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID) JOIN gibbonFormGroup ON (gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID) WHERE gibbonFamilyID=:gibbonFamilyID AND gibbonPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName ";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    while ($rowChild = $resultChild->fetch()) {

                            $dataInClass = array('gibbonCourseClassID' => $gibbonCourseClassID, 'gibbonPersonID' => $rowChild['gibbonPersonID']);
                            $sqlInClass = "SELECT * FROM gibbonCourseClassPerson WHERE gibbonCourseClassID=:gibbonCourseClassID AND gibbonPersonID=:gibbonPersonID AND role='Student'";
                            $resultInClass = $connection2->prepare($sqlInClass);
                            $resultInClass->execute($dataInClass);
                        if ($resultInClass->rowCount() == 1) {
                            $childInClass = true;
                            $rowInClass = $resultInClass->fetch();
                            $children[$count] = $rowInClass['gibbonPersonID'];
                            ++$count;
                        }
                    }
                }
            }
            if ($childInClass == true) {
                $role = 'Parent - Child In Class';
            }
        } else {
            // Check if in staff table as teacher
            $dataTeacher = array('gibbonPersonID' => $session->get('gibbonPersonID'));
            $sqlTeacher = "SELECT * FROM gibbonStaff WHERE gibbonPersonID=:gibbonPersonID AND type='Teaching'";
            $resultTeacher = $connection2->prepare($sqlTeacher);
            $resultTeacher->execute($dataTeacher);

            if ($resultTeacher->rowCount() == 1) {
                $role = 'Teacher';
                $dataRole = array('gibbonCourseClassID' => $gibbonCourseClassID, 'gibbonPersonID' => $session->get('gibbonPersonID'));
                $sqlRole = "SELECT * FROM gibbonCourseClassPerson WHERE gibbonCourseClassID=:gibbonCourseClassID AND gibbonPersonID=:gibbonPersonID AND role='Teacher'";
                $resultRole = $connection2->prepare($sqlRole);
                $resultRole->execute($dataRole);
                if ($resultRole->rowCount() >= 1) {
                    $role = 'Teacher - In Class';
                }
            }

            // Check if is student
            $dataStudent = array('gibbonPersonID' => $session->get('gibbonPersonID'), 'gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'));
            $sqlStudent = 'SELECT * FROM gibbonStudentEnrolment WHERE gibbonPersonID=:gibbonPersonID AND gibbonSchoolYearID=:gibbonSchoolYearID';
            $resultStudent = $connection2->prepare($sqlStudent);
            $resultStudent->execute($dataStudent);

            if ($resultStudent->rowCount() == 1) {
                $role = 'Student';
                $dataRole = array('gibbonCourseClassID' => $gibbonCourseClassID, 'gibbonPersonID' => $session->get('gibbonPersonID'));
                $sqlRole = "SELECT * FROM gibbonCourseClassPerson WHERE gibbonCourseClassID=:gibbonCourseClassID AND gibbonPersonID=:gibbonPersonID AND role='Student'";
                $resultRole = $connection2->prepare($sqlRole);
                $resultRole->execute($dataRole);
                if ($resultRole->rowCount() == 1) {
                    $role = 'Student - In Class';
                }
            }
        }

        return $role;
    }
}

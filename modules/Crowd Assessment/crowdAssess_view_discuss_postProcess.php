<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Data\Validator;
use Gibbon\Comms\NotificationSender;
use Gibbon\Domain\Planner\PlannerEntryGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Http\Url;

require_once __DIR__ . '/../../gibbon.php';

$_POST = $container->get(Validator::class)->sanitize($_POST, ['comment' => 'HTML']);

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$gibbonPlannerEntryID = $_GET['gibbonPlannerEntryID'] ?? '';
$gibbonPlannerEntryHomeworkID = $_GET['gibbonPlannerEntryHomeworkID'] ?? '';
$gibbonPersonID = $_GET['gibbonPersonID'] ?? '';

$URL = Url::fromModuleRoute('Crowd Assessment', 'crowdAssess_view_discuss')
    ->withQueryParams([
        'gibbonPlannerEntryID' => $gibbonPlannerEntryID,
        'gibbonPlannerEntryHomeworkID' => $gibbonPlannerEntryHomeworkID,
        'gibbonPersonID' => $gibbonPersonID,
    ]);

if (isActionAccessible($guid, $connection2, '/modules/Crowd Assessment/crowdAssess_view_discuss_post.php') == false) {
    header('Location: ' . $URL->withReturn('error0'));
} else {
    //Proceed!
    //Check if gibbonPlannerEntryID, gibbonPlannerEntryHomeworkID, and gibbonPersonID specified
    if ($gibbonPlannerEntryID == '' or $gibbonPlannerEntryHomeworkID == '' or $gibbonPersonID == '') {
        header('Location: ' . $URL->withReturn('error1'));
    } else {
        /** @var PlannerEntryGateway */
        $plannerEntryGateway = $container->get(PlannerEntryGateway::class);
        try {
            $result = $plannerEntryGateway->selectLessons(" AND gibbonPlannerEntryID=$gibbonPlannerEntryID");
        } catch (PDOException $e) {
            header('Location: ' . $URL->withReturn('error2'));
            exit();
        }

        if ($result->rowCount() != 1) {
            header('Location: ' . $URL->withReturn('error1'));
        } else {
            $row = $result->fetch();

            $comment = $_POST['comment'] ?? '';
            $role = getCARole($guid, $connection2, $row['gibbonCourseClassID']);

            if ($role == '' or empty($comment)) {
                header('Location: ' . $URL->withReturn('error2'));
            } else {
                /** @var CourseClassPersonGateway */
                $courseClassPersonGateway = $container->get(CourseClassPersonGateway::class);
                try {
                    $resultList = $courseClassPersonGateway->selectCrowdAssessStudents(
                        $role,
                        $row['gibbonCourseClassID'],
                        $row['homeworkCrowdAssessOtherTeachersRead'],
                        $row['homeworkCrowdAssessOtherParentsRead'],
                        $row['homeworkCrowdAssessSubmitterParentsRead'],
                        $row['homeworkCrowdAssessClassmatesParentsRead'],
                        $row['homeworkCrowdAssessOtherStudentsRead'],
                        $row['homeworkCrowdAssessClassmatesRead'],
                        " AND gibbonPerson.gibbonPersonID=$gibbonPersonID"
                    )->fetch();

                    if ($resultList->rowCount() != 1) {
                        header('Location: ' . $URL->withReturn('error2'));
                    } else {
                        //INSERT
                        $replyTo = !empty($_GET['replyTo']) ? $_GET['replyTo'] : null;


                        try {
                            $data = array('gibbonPlannerEntryHomeworkID' => $gibbonPlannerEntryHomeworkID, 'gibbonPersonID' => $session->get('gibbonPersonID'), 'comment' => $comment, 'replyTo' => $replyTo);
                            $sql = 'INSERT INTO gibbonCrowdAssessDiscuss SET gibbonPlannerEntryHomeworkID=:gibbonPlannerEntryHomeworkID, gibbonPersonID=:gibbonPersonID, comment=:comment, gibbonCrowdAssessDiscussIDReplyTo=:replyTo';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            header('Location: ' . $URL->withReturn('error2'));
                            exit();
                        }


                        //Work out who we are replying too
                        $replyToID = null;
                        $dataClassGroup = array('gibbonCrowdAssessDiscussID' => $replyTo);
                        $sqlClassGroup = 'SELECT * FROM gibbonCrowdAssessDiscuss WHERE gibbonCrowdAssessDiscussID=:gibbonCrowdAssessDiscussID';
                        $resultClassGroup = $connection2->prepare($sqlClassGroup);
                        $resultClassGroup->execute($dataClassGroup);
                        if ($resultClassGroup->rowCount() == 1) {
                            $rowClassGroup = $resultClassGroup->fetch();
                            $replyToID = $rowClassGroup['gibbonPersonID'];
                        }

                        //Get lesson plan name
                        $dataLesson = array('gibbonPlannerEntryID' => $gibbonPlannerEntryID);
                        $sqlLesson = 'SELECT * FROM gibbonPlannerEntry WHERE gibbonPlannerEntryID=:gibbonPlannerEntryID';
                        $resultLesson = $connection2->prepare($sqlLesson);
                        $resultLesson->execute($dataLesson);
                        if ($resultLesson->rowCount() == 1) {
                            $rowLesson = $resultLesson->fetch();
                            $name = $rowLesson['name'];
                        }

                        $homeworkNameSingular = $container->get(SettingGateway::class)->getSettingByScope('Planner', 'homeworkNameSingular');

                        $notificationSender = $container->get(NotificationSender::class);

                        //Create notification for homework owner, as long as it is not me.
                        if ($gibbonPersonID != $session->get('gibbonPersonID') and $gibbonPersonID != $replyToID) {
                            $notificationText = __('Someone has commented on your {homeworkName} for lesson plan "{lessonName}".', ['lessonName' => $name, 'homeworkName' => mb_strtolower(__($homeworkNameSingular))]);
                            $notificationSender->addNotification($gibbonPersonID, $notificationText, 'Crowd Assessment', "/index.php?q=/modules/Crowd Assessment/crowdAssess_view_discuss.php&gibbonPlannerEntryID=$gibbonPlannerEntryID&gibbonPlannerEntryHomeworkID=$gibbonPlannerEntryHomeworkID&gibbonPersonID=$gibbonPersonID");
                        }

                        //Create notification to person I am replying to
                        if (is_null($replyToID) == false) {
                            $notificationText = sprintf(__('Someone has replied to a comment on the {homeworkName} for lesson plan "{lessonName}".', ['lessonName' => $name, 'homeworkName' => mb_strtolower(__($homeworkNameSingular))]), $name);
                            $notificationSender->addNotification($replyToID, $notificationText, 'Crowd Assessment', "/index.php?q=/modules/Crowd Assessment/crowdAssess_view_discuss.php&gibbonPlannerEntryID=$gibbonPlannerEntryID&gibbonPlannerEntryHomeworkID=$gibbonPlannerEntryHomeworkID&gibbonPersonID=$gibbonPersonID");
                        }

                        $notificationSender->sendNotifications();

                        header('Location: ' . $URL->withReturn('success0')->withFragment($replyTo));
                    }

                } catch (\Exception $e) {
                    $page->addError($e->getMessage());
                }
            }
        }
    }
}

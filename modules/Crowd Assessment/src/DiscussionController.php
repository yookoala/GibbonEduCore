<?php

namespace Gibbon\Module\CrowdAssessment;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\CrowdAssessment\CrowdAssessDiscussGateway;
use Gibbon\Domain\Planner\PlannerEntryGateway;
use Gibbon\Domain\Planner\PlannerEntryHomeworkGateway;
use Gibbon\Domain\Timetable\CourseClassPersonGateway;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\View\Page;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class DiscussionController
{

    /**
     * @var PlannerEntryGateway
     */
    protected $plannerEntryGateway;

    /**
     * @var PlannerEntryHomeworkGateway
     */
    protected $plannerEntryHomeworkGateway;

    /**
     * @var CrowdAssessDiscussGateway
     */
    protected $crowdAssessDiscussGateway;

    /**
     * @var CourseClassPersonGateway
     */
    protected $courseClassPersonGateway;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Class constructor.
     *
     * @param PlannerEntryGateway          $plannerEntryGateway
     * @param PlannerEntryHomeworkGateway  $plannerEntryHomeworkGateway
     * @param CrowdAssessDiscussGateway    $crowdAssessDiscussGateway
     * @param CourseClassPersonGateway     $courseClassPersonGateway
     * @param StreamFactoryInterface       $streamFactory
     * @param Page                         $page
     * @param Connection                   $connection
     */
    public function __construct(
        PlannerEntryGateway $plannerEntryGateway,
        PlannerEntryHomeworkGateway $plannerEntryHomeworkGateway,
        CrowdAssessDiscussGateway $crowdAssessDiscussGateway,
        CourseClassPersonGateway $courseClassPersonGateway,
        StreamFactoryInterface $streamFactory,
        Page $page,
        Connection $connection
    )
    {
        $this->plannerEntryGateway = $plannerEntryGateway;
        $this->plannerEntryHomeworkGateway = $plannerEntryHomeworkGateway;
        $this->crowdAssessDiscussGateway = $crowdAssessDiscussGateway;
        $this->courseClassPersonGateway = $courseClassPersonGateway;
        $this->streamFactory = $streamFactory;
        $this->page = $page;
        $this->connection = $connection;
    }

    /**
     * View the discussions of a crowd assessment.
     *
     * Replace feature of legacy crowdAssess_view_discuss.php.
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface       $response
     * @param array                   $args
     * @return ResponseInterface
     */
    public function view(ServerRequestInterface $request, ResponseInterface $response, $args = []): ResponseInterface
    {
        $plannerEntryGateway = $this->plannerEntryGateway;
        $plannerHomeworkGateway = $this->plannerEntryHomeworkGateway;
        $crowdDiscussionGateway = $this->crowdAssessDiscussGateway;
        $courseClassPersonGateway = $this->courseClassPersonGateway;
        $streamFactory = $this->streamFactory;
        $page = $this->page;
        $connection2 = $this->connection;

        ob_start();
        if (isActionAccessible('', $connection2, '/modules/Crowd Assessment/crowdAssess_view_discuss.php') == false) {
            // Access denied
            $page->addError(__('You do not have access to this action.'));
        } else {
            //Get class variable
            $gibbonPersonID = $request->getQueryParams()['gibbonPersonID'] ?? '';
            $gibbonPlannerEntryID = $request->getQueryParams()['gibbonPlannerEntryID'] ?? '';
            $gibbonPlannerEntryHomeworkID = $request->getQueryParams()['gibbonPlannerEntryHomeworkID'] ?? '';

            $urlParams = ['gibbonPlannerEntryID' => $gibbonPlannerEntryID, 'gibbonPersonID' => $gibbonPersonID];
            $page->breadcrumbs
                ->add(__('View All Assessments'), 'crowdAssess.php')
                ->add(__('View Assessment'), 'crowdAssess_view.php', $urlParams)
                ->add(__('Discuss'));

            // Check required values
            if (empty($gibbonPersonID) || empty($gibbonPlannerEntryID) || empty($gibbonPlannerEntryHomeworkID)) {
                $page->addError(__('Student, lesson or homework has not been specified .'));
                return $response->withBody(
                    $streamFactory->createStream(
                        $page->render('index.twig.html')
                    )
                );
            }

            // Check existence of and access to this lesson.
            $lesson = $plannerEntryGateway->selectLessons(" AND gibbonPlannerEntryID=$gibbonPlannerEntryID")->fetch();
            if (empty($lesson)) {
                $page->addError(__('The selected record does not exist, or you do not have access to it.'));
                return $response->withBody(
                    $streamFactory->createStream(
                        $page->render('index.twig.html')
                    )
                );
            }

            $role = $courseClassPersonGateway->getCrowdAssessRole($lesson['gibbonCourseClassID']);

            // Get Student work
            try {
                $student = $courseClassPersonGateway->selectCrowdAssessStudents(
                    $role,
                    $lesson['gibbonCourseClassID'],
                    $lesson['homeworkCrowdAssessOtherTeachersRead'],
                    $lesson['homeworkCrowdAssessOtherParentsRead'],
                    $lesson['homeworkCrowdAssessSubmitterParentsRead'],
                    $lesson['homeworkCrowdAssessClassmatesParentsRead'],
                    $lesson['homeworkCrowdAssessOtherStudentsRead'],
                    $lesson['homeworkCrowdAssessClassmatesRead'],
                    " AND gibbonPerson.gibbonPersonID=$gibbonPersonID"
                )->fetch();
            } catch (\Exception $e) {
                $page->addError($e->getMessage());
            }

            if (empty($student)) {
                $page->addError(__('There is currently no work to assess.'));
                return $response->withBody(
                    $streamFactory->createStream(
                        $page->render('index.twig.html')
                    )
                );
            }

            $homework = $plannerHomeworkGateway->selectBy(['gibbonPlannerEntryID' => $gibbonPlannerEntryID, 'gibbonPersonID' => $gibbonPersonID])->fetch();

            // DETAILS
            $table = DataTable::createDetails('crowdAssessment');

            $table->addHeaderAction('add', __m('Add'))
                ->addParam('gibbonPersonID', $gibbonPersonID)
                ->addParam('gibbonPlannerEntryID', $gibbonPlannerEntryID)
                ->addParam('gibbonPlannerEntryHomeworkID', $gibbonPlannerEntryHomeworkID)
                ->setURL('/modules/Crowd Assessment/crowdAssess_view_discuss_post.php')
                ->displayLabel();

            $table->addColumn('name', __('Student'))
                ->format(Format::using('nameLinked', ['gibbonPersonID', '', 'preferredName', 'surname', 'Student', false, true]));
                $table->addColumn('read', __('Read'))
                ->format(function($student) {
                    if (empty($student)) return '';

                    if ($student['status'] == 'Exemption') {
                        $linkText = 'Exemption';
                    } elseif ($student['version'] == 'Final') {
                        $linkText = 'Final';
                    } else {
                        $linkText = 'Draft'.$student['count'];
                    }

                    if ($student['type'] == 'File' || $student['type'] == 'Link') {
                        $url = $student['type'] == 'File'  ? './'.$student['location'] : $student['location'];
                        $title = $student['version'].' '.sprintf(__('Submitted at %1$s on %2$s'), substr($student['timestamp'], 11, 5), Format::date($student['timestamp']));
                        return Format::link($url, $linkText, ['title' => $title]);
                    } else {
                        $title = sprintf(__('Recorded at %1$s on %2$s'), substr($student['timestamp'], 11, 5), Format::date($student['timestamp']));
                        return Format::tooltip($linkText, $title);
                    }
                });

            echo $table->render([$student + $homework]);
            echo '<br/>';


            // DISCUSSION - recursive
            $getDiscussion = function ($gibbonPlannerEntryHomeworkID, $urlParams, $parent = null, $level = null) use (&$getDiscussion, &$crowdDiscussionGateway) {
                $discussion = [];
                $items = $crowdDiscussionGateway->selectDiscussionByHomeworkID($gibbonPlannerEntryHomeworkID, $parent)->fetchAll();
                foreach ($items as $item) {
                    $item['replies'] = $getDiscussion($gibbonPlannerEntryHomeworkID, $urlParams, $item['gibbonCrowdAssessDiscussID'], $level + 1);

                    if ($level < 3) {
                        $item['attachmentLocation'] = "index.php?q=/modules/Crowd Assessment/crowdAssess_view_discuss_post.php&".http_build_query($urlParams)."&gibbonPlannerEntryHomeworkID=$gibbonPlannerEntryHomeworkID&replyTo=".$item['gibbonCrowdAssessDiscussID'];
                        $item['attachmentText'] = __('Reply');
                        $item['attachmentTarget'] = '';
                    }
                    $discussion[] = $item;
                }

                return $discussion;
            };

            $discussion = $getDiscussion($homework['gibbonPlannerEntryHomeworkID'], $urlParams, 0);

            echo $page->fetchFromTemplate('ui/discussion.twig.html', [
                'title' => '',
                'compact' => true,
                'discussion' => $discussion
            ]);
        }

        $body = $streamFactory->createStream(ob_end_clean());
        return $response->withBody($body);
    }
}

<?php

class CrowdAssessmentCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    /**
     * Try to create homework for assessment.
     *
     * @param AcceptanceTester $I
     *
     * @return void
     */
    public function tryToCreateHomeworkForAssessment(AcceptanceTester $I)
    {
        /**
         * If you go into the Planner,
         *  1. Add a new Lesson plan.
         *  2. Pick a demo Class that you "teach".
         *  3. Make sure the lesson plan date is in the past week or so (not the future),
         *  4. then enable the Homework option with a deadline in the future,
         *  5. then enable Online Submission, and finally enable the Crowd Assessment option.
         *  6. This should create a crowd-assessable lesson.
         *  7. Once created, click to View your lesson plan and you can manually add a submission for a student using the Edit option.
         *  8. Once a student has a submission it should show up in the Crowd Assessment module list.
         */
        $I->loginAsTeacher();
        $I->amOnModulePage('Planner', 'planner.php', [
            'viewBy' => 'date',
            'date' => (new \DateTime('this monday'))->format('Y-m-d'),
        ]);

        $I->amGoingTo('Add a new Lesson plan with a homework due in future with crowd assessment enabled.');
        $I->seeLink('Add');
        $I->click('Add');

        // Create a lesson plan for a past lesson.
        $I->selectOption('select[name=gibbonCourseClassID]', 'AR07.1');
        $I->fillField('input[name=name]', 'Testing Lesson Last Week'); // Lesson Name
        $I->fillField('textarea[name=homeworkDetails]', 'This is a homework created for testing');
        $I->fillField('input[name=date]', (new \DateTime('last monday'))->format('d/m/Y'));
        $I->fillField('input[name=timeStart]', '14:00');
        $I->fillField('input[name=timeEnd]', '15:00');

        // Enable homework with due date in the future.
        // Set homework deadline to 2 weeks from now.
        $I->selectOption('input[name=homework]', 'Y');
        $I->fillField('input[name=homeworkDueDate]', (new \DateTime('2 weeks'))->format('d/m/Y'));
        $I->fillField('input[name=homeworkDueDateTime]', '08:00');

        // Enable online submission.
        // Set submission open date in the past.
        $I->selectOption('input[name=homeworkSubmission]', 'Y');
        $I->fillField('input[name=homeworkSubmissionDateOpen]', (new \DateTime('yesterday'))->format('d/m/Y'));

        // Enable crowd assessment.
        $I->selectOption('input[name=homeworkCrowdAssess]', 'Y');

        // Submit the form.
        $I->submitForm('#action', [], 'Submit');

        // Check result.
        $I->expectTo('See a lesson plan with the information I just filled in.');
        $I->see('Your request was completed successfully. You can edit your newly created record here.', '.success');
        $I->seeLink('here');
        $I->click(['link' => 'here'], '.success');
        // TODO: add field value tests.
        // TODO: start crowd assessment.
    }

    /**
     * Do crowd assessment as a student.
     *
     * @depends tryToCreateHomeworkForAssessment
     * @param AcceptanceTester $I
     *
     * @return void
     */
    public function tryToDoCrowdAssessment(AcceptanceTester $I)
    {

    }
}

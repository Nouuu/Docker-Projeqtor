.. include:: ImageReplacement.txt

.. title:: Requirements and Tests

.. index:: Requirement

.. _requirement:

Requirements
------------

.. sidebar:: Concepts 

   * :ref:`product-concept`

Requirement is a rule defined for a project or a product.

In most IT projects, requirement can be a functional rule for a software.

It allows to define and monitor cost and delays.

It can be linked to test cases, it's used to describe how you will test that a given requirement.

.. rubric:: Rights management

* Linking requirements to a project will limit the visibility, respecting rights management at project level.

.. rubric:: Requirement link to test cases

* Test cases can be linked to a requirement in **linked element section**.
* Linking a requirement to a test case will display a summary of test case run (defined in test session).
* This way, you will have an instant display of test coverage for the requirement.

.. rubric:: Requirement link to tickets

* When test case run status is set to **failed**, the reference to a ticket must be defined (reference to the incident).
* When the requirement is linked to a test case with this run status, ticket is automatically linked to the requirement. 

.. rubric:: Predecessor and successor elements

* Requirements can have predecessors and successors.
* This defines some dependencies on the requirements.
* The dependencies don’t have specific effects. It is just an information.

.. rubric:: Monitoring indicator

* Possibility to define indicators to follow the respect of dates values.

  .. describe:: Respect of initial due date

  .. describe:: Respect of planned due date

.. sidebar:: Other sections

   * :ref:`Summary of test cases<summary-test-case-section>`
   * :ref:`Predecessor and Sucessor<predSuces-element-section>`
   * :ref:`Linked element<linkElement-section>`   
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>`   
   
.. rubric::  Description

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :widths: 40, 60
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the requirement
   * - |RequiredField| Name
     - Short description of the requirement.
   * - |RequiredField| Requirement type
     - Type of requirement.
   * - Project
     - The project concerned by the requirement.
   * - Product
     - The product concerned by the requirement.
   * - :term:`External reference`
     - External reference for the requirement.
   * - :term:`Requestor`
     - Contact who requested the requirement.
   * - :term:`Origin`
     - Element which is the origin of the requirement.
   * - Urgency
     - Urgency of implementation of the requirement.
   * - Initial due date
     - Initial due date.
   * - Planned due date
     - Planned due date.
   * - |RequiredField| Description
     - Long :term:`description` of the requirement.

.. topic:: Fields Project and Product

   * Must be concerned either with a project, a product or both.
   * If the project is specified, the list of values for field "Product" contains only products linked the selected project.

.. rubric::  Treatment

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :widths: 40, 60
   :header-rows: 1

   * - Field
     - Description
   * - Top requirement
     - Parent requirement, defining a hierarchic structure.
   * - |RequiredField| Status
     - Actual :term:`status` of the requirement.
   * - :term:`Responsible`
     - Resource who is responsible for the requirement.
   * - Criticality
     - Level of criticality of the requirement for the product.
   * - Feasibility
     - Result of first analysis to check the feasibility of the implementation of the requirement.
   * - Technical risk
     - Result of first analysis to measure the technical risk of the implementation of the requirement.
   * - Estimated effort
     - Result of first analysis to measure the estimated effort of the implementation of the requirement.
   * - :term:`Handled`
     - Box checked indicates the requirement is taken over.
   * - :term:`Done`
     - Box checked indicates the requirement has been treated.
   * - :term:`Closed`
     - Box checked indicates the requirement is archived.
   * - Cancelled
     - Box checked indicates the requirement is cancelled.
   * - Target version
     - Version of the product for which this requirement will be active.
   * - :term:`Result`
     - Description of the implementation of the requirement. 
 
.. topic:: Field Target version

   Contains the list of product versions available according to the project and product selected.
    
.. rubric::  Lock

A requirement can be locked to ensure that its definition has not changed during the implementation process.

   **Button: Lock/Unlock requirement**

   * Button to lock or unlock the requirement to preserve it from being changed.
   * Only the user who locked the requirement or a habilitated user can unlock a requirement.

   **Requirement locked**

   * When a requirement is locked the following fields are displayed.


.. tabularcolumns:: |l|l|

.. list-table:: Fields when the requirement is locked
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - Locked
     - Box checked indicates the requirement is locked.
   * - Locked by
     - User who locked the requirement.
   * - Locked since
     - Date and time when the requirement was locked.

.. raw:: latex

    \newpage

.. index:: Test case

.. _test-case:

Test cases
----------

.. sidebar:: Concepts 

   * :ref:`product-concept`

Test cases are elementary actions executed to test a requirement.

You may define several tests to check a requirement, or check several requirements with one test.

The test case is defined for a project, a product or one these components.


.. rubric:: Rights management

* Linking test case to a project will limit the visibility, respecting rights management at project level.


.. sidebar:: Other sections

   * :ref:`Predecessor and Sucessor element<predSuces-element-section>`
   * :ref:`Linked element<linkElement-section>`   
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>`   

.. rubric:: Predecessor and successor elements

* Test case can have predecessors and successors.
* This defines some dependencies on the test case.
* Dependencies don’t have specific effects. It is just an information.

.. rubric:: Description

This section allow you to describe the test to run.


 .. compound:: Fields Project and Product

   * Must be concerned either with a project, a product or both.
   * If the project is specified, the list of values for field "Product" contains only products linked the selected project.

 .. compound:: Field Version

   * Contains the list of product and component versions available according to the project and product selected.

 .. compound:: Field Environment (Context)

   * Contexts are initialized for IT Projects as “Environment”, “OS” and “Browser”. 
   * This can be easily changed values in :ref:`context` screen.  

 .. compound:: Field Description

   * The description of test case should describe the steps to run the test.


.. rubric::  Treatment

.. tabularcolumns:: |l|l|

.. list-table::
   :header-rows: 1

   * - Field
     - Description
   * - Parent test case
     - Parent test case, defining a hierarchic structure for test cases.
   * - |RequiredField| Status
     - Actual :term:`status` of the requirement.
   * - :term:`Responsible`
     - Resource who is responsible of the test case.
   * - Priority
     - Level of priority for the test case.
   * - :term:`Handled`
     - Box checked indicates the test case is taken over.
   * - :term:`Done`
     - Box checked indicates the test case has been treated.
   * - :term:`Closed`
     - Box checked indicates the test case is archived.
   * - Cancelled
     - Box checked indicates the test case is cancelled.
   * - Prerequisite
     - List of steps that must be performed before running the test.	
   * - :term:`Expected result<Result>`
     - Description of expected result of the test.
 
.. topic:: Field Prerequisite

   If left blank and test case has a parent, parent prerequisite will automatically be copied here. 


.. index:: Test case (Run status)

.. _test-case-run-status:

.. rubric:: Test case run

* |Planned| **Planned:** Test to be executed.
* |Passed| **Passed:** Test passed with success (result is conform to expected result).
* |Blocked| **Blocked:** Impossible to run the test because of a prior incident  (blocking incident or incident on preceding test) or missing prerequisite.
* |Failed| **Failed:** Test has returned wrong result.

* This section allows to display a complete list of test case runs.
* These are links of the test to test sessions.
* This list also displays the current status of the test in the sessions.

.. topic:: Field Summary

   * An icon whose presents the run status of the test case.
   * For detail, see: :ref:`Summary of test case run status<summary-test-case-run-status>`. 


.. note ::

   To go, click on the corresponding test session.


.. tabularcolumns:: |l|l|

.. list-table:: Fields - Test case runs list
   :header-rows: 1

   * - Field
     - Description
   * - Test session
     - Composed of session type, Id and description. 
   * - Status
     - Current status of the test case run in the test session.

.. raw:: latex

    \newpage

.. index:: Test session

.. _test-session:

Test sessions
-------------

.. figure:: /images/GUI/REQUIREMENT_SCR_TestSession.png
   :alt: Test session screen
   
   Test session screen
   
   
.. sidebar:: Concepts 

   * :ref:`product-concept`

A test session defines all the tests to be executed to reach a given target.

Define in the test case runs all test cases will be running to this test session.

For each test case run sets the status of test results. (See: :ref:`Test case run status<test-case-run-status>`)

The test session is defined for a project, a product or one these components.

.. rubric:: Rights management

* Linking test session to a project will limit the visibility, respecting rights management at project level.

.. rubric:: Test sessions regroupment

* Test session can have parents to regroup test sessions.

.. rubric:: Planning element

* A test session is a planning element like activity.
* A test session is a task in a project planning.
* Allows to assigned resource and follow up progress.

.. rubric:: Predecessor and successor elements

* Test sessions can have predecessors and successors.
* This defines some dependencies on test cases or planning constraints.

.. rubric:: Monitoring indicator

* The indicators can be defined in the :ref:`List of Values<list-of-values>`.
* See: :ref:`health-status` and :ref:`overall-progress`

.. sidebar:: Other sections

   * :ref:`Assignment<assignment-section>`
   * :ref:`Progress<progress-section>`
   * :ref:`Summary of test cases<summary-test-case-section>`
   * :ref:`Predecessor and Sucessor element<predSuces-element-section>`
   * :ref:`Linked element<linkElement-section>`   
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>`   

.. rubric:: Description 

.. tabularcolumns:: |l|l|

.. list-table::
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the test session.
   * - |RequiredField| Name
     - Short description of the test session.
   * - |RequiredField| Session type
     - Type of test session.
   * - Project
     - The project concerned by the test session.
   * - Product
     - The product concerned by the test session.
   * - Version
     - Version of the product or component concerned by the test session.
   * - :term:`External reference`
     - External reference for the test session.
   * - :term:`Description`
     - Complete description of the test session.

.. topic:: Fields Project and Product

   * Must be concerned either with a project, a product or both.
   * If the project is specified, the list of values for field "Product" contains only products linked the selected project.

.. topic:: Field Version

   * Contains the list of product and component versions available according to the project and product selected.

.. rubric:: Treatment

.. tabularcolumns:: |l|l|

.. list-table::
   :header-rows: 1

   * - Field
     - Description
   * - Parent activity
     - Parent activity, to define hierarchic position in the Gantt.
   * - Parent session
     - Parent session, to define session of sessions.
   * - |RequiredField| Status
     - Actual :term:`status` of the test session.
   * - :term:`Responsible`
     - Resource who is responsible of the test session.
   * - :term:`Handled`
     - Box checked indicates the test session is taken over.
   * - :term:`Done`
     - Box checked indicates the test session has been treated.
   * - :term:`Closed`
     - Box checked indicates the test session is archived.
   * - Cancelled
     - Box checked indicates the test session is cancelled.
   * - :term:`Result`
     - Summary result of the test session. 
 
.. raw:: latex

    \newpage

.. rubric:: Test case runs

This section allows to manage test case runs.

.. list-table:: Fields - Test case runs list
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - Test case
     - Information about test case (type, Id and name).
   * - Detail
     - Detail of test case.
   * - Status
     - Status of test case run.

.. topic:: Field Test case
   
   * This icon |Comment| appears when the test case run comment field is filled.
   * Moving the mouse over the icon will display the test case run comments.

.. topic:: Field Detail

   * Moving the mouse over the icon |Description| will display the test case description.
   * Moving the mouse over the icon |Result| will display the test case expected result.
   * Moving the mouse over the icon |Prerequisite| will display the test case prerequisite. 

.. topic:: Field Status
 
   * If status of test case run is **failed**, information about selected ticket is displayed too.


.. raw:: latex

    \newpage


.. rubric:: Test case runs list management

* Click on |Add| to add a test case run. The **Test case run dialog box** will be appear.
* Click on |Edit| to edit a test case run. The **Test case run detail dialog box** will be appear.
* Click on |Delete| to remove a test case run.
* Click on |Passed| to mark test case run as passed.
* Click on |Failed| to mark test case run as failed. The **Test case run detail dialog box** will be appear.
* Click on |Blocked| to mark test case run as blocked.


.. figure:: /images/GUI/REQUIREMENT_ZONE_TestCaseRun-Session.png
   :alt: Test case run section
   
   Test case run section



.. note:: 
   * When status is set to failed, the reference to a ticket must be defined (reference to the incident).
   * The referenced ticket is automatically added in linked element. 
   * Field ticket appear only whether status of test case run is **failed**.


.. raw:: latex

    \newpage


.. _summary-test-case-section:

Summary of test cases section
-----------------------------

.. figure:: /images/GUI/REQUIREMENT_ZONE_SumaryTestCase.png
   :alt: Summary test cases
   
   Summary test cases
   
   
This section summarizes the status of test case runs to requirement and test session.

 .. compound:: Requirement

   Summarizes the status of test case runs for test cases are linked to the requirement.

   Because a test case can be linked to several test sessions, total can be greater than linked to the requirement.

 .. compound:: Test session

   Summarizes the status of test case runs in the test session.


.. _summary-test-case-run-status:

.. rubric:: Summary of test case run status

* |Planned| **Planned:** No test failed or blocked, at least one test planned.
* |Passed| **Passed:** All tests passed.
* |Failed| **Failed:** At least one test failed.
* |Blocked| **Blocked:** No test failed, at least one test blocked.






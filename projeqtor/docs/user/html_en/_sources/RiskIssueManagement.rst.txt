.. include:: ImageReplacement.txt

.. title:: Risk & Issue Management

.. index:: Risk 

.. _risk:

Risks
-----

A risk is any threat of an event that may have a negative impact to the project, and which may be neutralized, or at least minimized, through pre-defined actions.

The risk management plan is a key point to project management :

 - Identify risks and estimate their severity and likelihood.
 - Identify mitigating actions.
 - Identify opportunities.
 - Follow-up actions.
 - Identify risks that finally occur (becoming an issue).

.. index:: Risk (Contingency reserve)

.. rubric:: Contingency reserve

* Contingency reserve is defined according to monetary impact and likelihood of occurrence.
* Contingency reserve for risks and potential gain for opportunities allow to define the project reserve. (See: :ref:`Project reserve<project-reserve>`)

.. rubric:: Monitoring indicator

* Possibility to define indicators to follow the respect of dates values.

 .. describe:: Respect of initial due date
 .. describe:: Respect of planned due date

.. sidebar:: Other sections

   * :ref:`Linked element<linkElement-section>`   
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>`   

.. rubric:: Section Description

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :widths: 40, 60
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the risk.
   * - |RequiredField| Name
     - Short description of the risk.
   * - |RequiredField| Type
     - Type of risk.
   * - |RequiredField| Project
     - The project concerned by the risk.
   * - Severity
     - Level of importance of the impact for the project.
   * - Likelihood
     - Probability level of the risk to occur.
   * - Criticality
     - Global evaluation level of the risk.
   * - Cost of impact
     - Impact cost of the risk.
   * - Project reserved cost
     - The reserve amount according to the risk likelihood.
   * - :term:`Origin`
     - Element which is the origin of the risk.
   * - Cause
     - Description of the event that may trigger the risk.
   * - Impact
     - Description of the estimated impact on the project if the risk occurs.
   * - :term:`Description`
     - Complete description of the risk.
     
.. topic:: Field Criticality

   Automatically calculated from Severity and Likelihood values. See: :ref:`criticality-calculation`. Value can be changed. 

.. topic:: Field Project reserved cost

   Automatically calculated from the percentage defined for the selected likelihood. (See: :ref:`likelihood`)


.. rubric:: Section Treatment

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - |RequiredField| Status
     - Actual :term:`status` of the risk.
   * - :term:`Responsible`
     - Resource who is responsible for the treatment of the risk.
   * - Priority
     - Expected priority to take into account this risk.
   * - Initial end date
     - Initially expected end date of the risk.
   * - Planned end date
     - Updated end date of the risk.
   * - :term:`Handled`
     - Flag to indicate that risk is taken into account.
   * - :term:`Done`
     - Flag to indicate that risk has been treated.
   * - :term:`Closed`
     - Flag to indicate that risk is archived.
   * - Cancelled
     - Flag to indicate that risk is cancelled.
   * - :term:`Result`
     - Complete description of the treatment done on the risk.  
 
.. raw:: latex

    \newpage

.. index:: Opportunity 

.. _opportunity:

Opportunities
-------------

.. sidebar:: Other sections

   * :ref:`Linked element<linkElement-section>`   
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>` 
   
An opportunity can be seen as a positive risk. It is not a threat but the opportunity to have a positive impact to the project.

.. index:: Opportunity (Potential gain) 
   
.. rubric:: Potential gain

* The potential gain is defined according to the expected amount and likelihood of occurrence.
* Contingency reserve for risks and potential gain for opportunities allow to define the project reserve. 
* See: :ref:`Project reserve<project-reserve>`
 

.. rubric:: Section Description

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend| 
   :widths: 30, 50
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the opportunity.
   * - |RequiredField| Name
     - Short description of the opportunity.
   * - |RequiredField| Type
     - Type of opportunity.
   * - |RequiredField| Project
     - The project concerned by the opportunity.
   * - Significance
     - Level of importance of the impact for the project.
   * - Likelihood
     - Evaluation of the estimated improvement, or positive impact, on the project of the opportunity.
   * - Criticality
     - Global evaluation level of the opportunity.
   * - Expected improvement
     - Expected amount of the opportunity.
   * - Project reserved gain
     - The estimated gain, according to the opportunity likelihood.
   * - :term:`Origin`
     - Element which is the origin of the opportunity.
   * - Opportunity source
     - Description of the event that may trigger the opportunity.
   * - Impact
     - Description of the estimated positive impact on the project.
   * - :term:`Description`
     - Complete description of the opportunity.

.. topic:: Field Criticality

   Automatically calculated from Significance and Likelihood values. See: :ref:`criticality-calculation` - Value can be changed. 

.. topic:: Field Project reserved gain

   Automatically calculated from the percentage defined for the selected likelihood. (See: :ref:`likelihood`)

.. rubric:: Section Treatment

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend| 
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - |RequiredField| Status
     - Actual :term:`status` of the opportunity.
   * - :term:`Responsible`
     - Resource who is responsible for the opportunity.
   * - Priority
     - Expected priority to take into account this opportunity.
   * - Initial end date
     - Initially expected end date of the opportunity.
   * - Planned end date
     - Updated end date of the opportunity.
   * - :term:`Handled`
     - Flag to indicate that opportunity is taken into account.
   * - :term:`Done`
     - Flag to indicate that opportunity has been treated.
   * - :term:`Closed`
     - Flag to indicate that opportunity is archived.
   * - Cancelled
     - Flag to indicate that opportunity is cancelled.
   * - :term:`Result`
     - Complete description of the treatment of the opportunity.  
 
.. raw:: latex

    \newpage

.. _criticality-calculation:

Criticality value calculation
-----------------------------

Criticality value is automatically calculated from **Severity (Significance)** and **Likelihood** values.

Criticality, Severity (Significance) and Likelihood values are defined in lists of values screens. 

See: :ref:`criticality`, :ref:`severity` and :ref:`likelihood` screens.

In the previous screens, a name of value is set with numeric value.  

Criticality numeric value is determined by a simple equation as follows:

.. topic:: Equation

   * [Criticality value] = [Severity value] X [Likelihood value] / 2
   * For example:

     * Critical (8) = High (4) X High (4) / 2

.. rubric:: Default values

* Default values are determined.
* You can change its values while respecting the equation defined above. 


.. raw:: latex

    \newpage

.. index:: Issue 

.. _issue:

Issues
------

An issue is a problem that occurs during the project.

If the risk Management plan has been correctly managed, issues should always be occurring identified risks.

Actions must be defined to solve the issue.

.. rubric:: Monitoring indicator

* Possibility to define indicators to follow the respect of dates values.

 .. describe:: Respect of initial due date
 .. describe:: Respect of planned due date


.. sidebar:: Other sections

   * :ref:`Linked element<linkElement-section>`   
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>`   

.. rubric:: Section Description

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend| 
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the issue.
   * - |RequiredField| Name
     - Short description of the issue.
   * - |RequiredField| Type
     - Type of issue.
   * - |RequiredField| Project
     - The project concerned by the issue.
   * - Criticality
     - Level of importance of the impact for the project.
   * - Priority
     - Priority requested to the treatment of the issue.
   * - :term:`Origin`
     - Element which is the origin of the issue.
   * - Cause
     - Description of the event that led to the issue.
   * - Impact
     - Description of the impact of the issue on the project.
   * - :term:`Description`
     - Complete description of the issue.

.. rubric:: Section Treatment

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend| 
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - |RequiredField| Status
     - Actual :term:`status` of the issue.
   * - :term:`Responsible`
     - Resource who is responsible for the issue.
   * - Initial end date
     - Initially expected end date of the issue.
   * - Planned end date
     - Updated end date of the issue.
   * - :term:`Handled`
     - Flag to indicate that issue is taken into account.
   * - :term:`Done`
     - Flag to indicate that issue has been treated.
   * - :term:`Closed`
     - Flag to indicate that issue is archived.
   * - Cancelled
     - Flag to indicate that issue is cancelled.
   * - :term:`Result`
     - Complete description of the treatment of the issue.  
 
.. raw:: latex

    \newpage

.. index:: Action 


.. _action:

Actions
-------

An action is a task or activity that is set-up in order to :

 - Reduce the likelihood of a risk
 - or reduce the impact of a risk
 - or solve an issue
 - or build a post-meeting action plan
 - or just define a “to do list”.

The actions are the main activities of the risk management plan.

They must be regularly followed-up.

.. rubric:: Private action

* Private actions allow to manage a personal to-do list.


.. rubric:: Monitoring indicator

* Possibility to define indicators to follow the respect of dates values.

 .. describe:: Respect of initial due date
 .. describe:: Respect of planned due date

.. sidebar:: Other sections

   * :ref:`Linked element<linkElement-section>`   
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>`   

.. rubric:: Section Description

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend| 
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the action.
   * - |RequiredField| Name
     - Short description of the action.
   * - |RequiredField| Action type
     - Type of action.
   * - |RequiredField| Project
     - The project concerned by the action.
   * - Priority
     - Priority requested to the treatment of the action.
   * - Private
     - Box checked allows to define a private action.
   * - :term:`Description`
     - Complete description of the action.

.. rubric:: Section Treatment

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend| 
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - |RequiredField| Status
     - Actual :term:`status` of the action.
   * - :term:`Responsible`
     - Resource who is responsible for the action.
   * - Initial due date
     - Initially expected end date of the action.
   * - Planned due date
     - Updated end date of the action.
   * - :term:`Handled`
     - Box checked indicates that the action is taken over.
   * - :term:`Done`
     - Box checked indicates that the action has been treated.
   * - :term:`Closed`
     - Box checked indicates that the action is archived.
   * - Cancelled
     - Box checked indicates that the action is cancelled.
   * - Efficiency
     - Evaluation of the efficiency the action had on the objective (for instance on the risk mitigation).
   * - :term:`Result`
     - Complete description of the treatment of the action.  


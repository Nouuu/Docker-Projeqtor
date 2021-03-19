.. include:: ImageReplacement.txt

.. title:: Concepts

.. _concept:

********
Concepts
********

.. index: Project (concept)

.. _Concept_project:

Project
*******

A project is the main entity of ProjeQtOr.

Project element is more than a :ref:`planning-element`.


.. rubric:: Gather all project data

It is also used to allows to gather all data depend on project:

 * Planning element
 * Risk assessment, Risk mitigation, Reserve
 * Ticket, Issue, Bug tracking, Change request, Support
 * Steering, Meeting, Decision, Action plan
 * Requirement & Test 
 * Project expense
 * Quotation, Order, Bill, Payment
 * Document


.. rubric:: Restrict data visibility
   
Allows restricting data visibility to users by project.
   
The project data visibility is granted according to the user profile.
   
   
.. seealso:: 
      
   :ref:`profiles-definition` and :ref:`allocation-to-project`


.. rubric:: Project selector
      
The project selector works as a filter.
          
By default, the selector displays "all projects", you can change this view in the user parameters and choose the project to display by default.
          
You can restrict data for one or more dedicated projects without necessarily being bound
       
.. seealso:: 
         
   :ref:`top-bar` and :ref:`user-parameters`


.. index:: Project (type)
    
.. _project_type_definition:

The project type
----------------

.. compound:: Three project types can be defined:
   
      **1 - Operational project**
                     
         Most common project to follow activity.
                        
         Manual Billed, Fixed price, Capped time, Time & materials, Internal are operationals projects
                  
      **2 - Administrative project**
                  
         Allows to follow the non productive work as holidays, sickness, training, …
                        
         All resources have access to this project type without being assigned (project) or assigned (Activity).
                        
         Create an activity, like an OPE project, for each type of absence.
                     
      **3 - Template project**
                  
         Designed to define templates, to be copied as operational project.
                        
         Any project manager can copy these projects without having to be assigned to them. On the other hand they will not be able to modify it and will have to copy the project in OPE for that.

         For modifying a template project, the modifier must be assigned to the project..
   
         See: :ref:`Copy an item<copy-item>`
         
.. note:: 

   The project type is defined in a project type.
   
   Which is associated to a project. See: :ref:`planningelem_project`
   
   
   

.. rubric:: Define billable project

A project can be billable or not.

 .. compound:: **Non billable project**

    The non billable project is used for internal or administrative project.

 .. compound:: **Billable project**

    For billable projects the billing types available are: at terms, on producing work, on capping produced work and manual.
    
    The actual work done and billed is locked

.. note:: 

   The project billing type is defined in a project type.
   
   Which is associated to a project. See: :ref:`planningelem_project` 


.. warning:: 

   When deleting a project, also delete the indicators, Emails and Delays for the tickets of this project



.. _hat-project:

.. rubric:: Hat project

The definition is made by the project type, you define that certain types of projects can not have activities, only subprojects or milestones..

.. figure:: /images/GUI/CONCEPT_ZONE_Behavior.png
   :alt: Project type behavior
   
   Project type Behavior



.. rubric:: Fix the planning

you can freeze the content of a project or projects from the moment you do not want to extend the project or modify it.


.. raw:: latex

    \newpage







.. _allocation-to-project:

Allocation to project
---------------------

.. sidebar:: Concepts 

   * :ref:`profiles-definition`
   * :ref:`user-ress-contact-demystify`
   
Allocation to project is used to:

* Define project data visibility.
* Define resource availability.
* Define the period of access to project data by the user. 



.. tabularcolumns:: |l|l|

.. list-table:: Required field |ReqFieldLegend| 
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id of the resource.
   * - Resource
     - Resource name.
   * - Or contact
     - Contact name.
   * - Or user
     - User name.  
   * - |RequiredField| Profile
     - Selected profile.
   * - |RequiredField| Project
     - Project allocated to.
   * - Rate
     - Allocation rate for the project (%).
   * - Start date
     - Start date of allocation.
   * - End date
     - End date of allocation.
   * - :term:`Closed`
     - Flag to indicate that the allocation is archived.
   * - :term:`Description`
     - Complete description of the allocation.



.. rubric:: Allocation behavior

**Example 1**

* R allocated to project A
* B is a subproject of A 
* B has no allocation

   * R can be assigned to an activity of B
   * It is impossible to delete the allocation of R on project A because R is assigned on the activity of project B

**Example 2**

* R allocated to projects A and B
* B is a subproject of A
* R is assigned to the activity of B


   * It is possible to remove the allocation from R to B because the allocation on A preserves coherence
   * A being the parent project, it is not possible to delete the allocation from R to A although the allocation from R to B exists 



.. topic:: Fields Resource & Contact

   * You can select resource or contact.
   * If none is selected then the user connected is used to define the allocation.
   * If a contact is a resource and inversely, then resource or contact name will be selected too.


The following sections describe allocation to project, performed for user, resource or contact.

User allocation
---------------

Allocation to project gives data visibility on a project.

Allocation to project can be defined in the :ref:`user` screen.

.. rubric:: Profile selection

The selected profile allows you to define the rights on all the elements of the project.

The profile displayed first will be the default

The profile given to an assignment for a user / resource / contact may be different on each project
   
These rights are applied only on the said project   
   
.. note:: 

   Profile defined in allocation to project does not grant or revoke access to users.
      
   General access to application functionalities and data is defined by user profile. 

.. rubric:: Period selection

Allows you to define the visibility period of the project data.

Can be used to limit the access period, in accordance with the service agreement.



.. raw:: latex

    \newpage

.. _resource-allocation-to-project:

Resource allocation to project
------------------------------

Allocation to project allows to define the resource availability on project.

A resource may be allocated to projects at a specified rate for a period.

Allocation to project can be defined in :ref:`planningelem_project` and :ref:`resource` screens.

You can also allocate a team or an organization to a project in :ref:`team` and :ref:`Organization<linked-resource-organization>` screens.


.. note::

   A resource allocated to a project can be defined as :term:`responsible` of project items treatment.

.. _PeriodandRate:

.. rubric:: Period & Rate selection

A resource may be allocated to a project or assigned to a task at a specified rate for a period.

This rate is used to keep some scheduling time for other tasks.

For instance, if rate is 50%, the resource will not be planned more than half days on the task.
 
If the period is not specified then the resource is allocated throughout the project.

.. warning::

    The planning calculator tries to plan, the remaining work on the task assigned to a resource within the allocation to project period.
    
    If remaining work on the task can't be planned, a purple bar appears in the Gantt view.

.. rubric:: Change resource on an allocation to project

A resource can be changed on allocation to project.

All tasks assigned to old resource will be transferred to the new resource with planned work and remaining work.

Work done on tasks belongs to always the old resource.


Multi-allocation to project
---------------------------

A resource can be allocated to multiple projects in the same period.

Make sure that the allocation to projects for a period not exceeding 100%.

In the section **Allocations** in :ref:`resource` screen, a tool allows to displayed conflicts.

.. tip:: How resolve conflicts?

   You can change allocation period to avoid overlap between projects.
   
   You can change the rate of allocation for it does not exceed 100% for the period.


Contact allocation to project
-----------------------------

A contact allocated to a project can be defined as :term:`requestor`.

Allocation to project can be defined in :ref:`planningelem_project` and :ref:`contact` screens.



.. _concept_activity:

Activity
********

An activity is a kind of task that can be planned or that groups other activities.

This is usually a long-term task that can be assigned to one or more resources. 

Activities will appear on the Gantt schedule view.

For example, you might consider an activity:

* Scheduled tasks,
* Modification requests,
* The phases,
* Versions or new deployments,

Activities can be grouped as a Mother / Daughter link. 

The parent activity must belong to the same project. 

A WBS structure is applied and a dynamic index is calculated for all activities. 

The WBS Activity Index can be changed in the Gantt schedule view using drag and drop.


.. topic:: Planning Activity 

   An activity can be linked to elements that cannot be planned.
   So that the time spent on tickets, which cannot be scheduled, can be taken into account in the overall planning of the project, you can create a planning activity.

   This option allows you to assign a time pool that will be scheduled and to link tickets to this tank.

   The time spent on tickets will then be decremented to that of the planning activity.

   See: :ref:`ticket-planning-activity`
   
   
   
.. raw:: latex

    \newpage

.. _assignment:

Assignment
----------

The assignment is used to assign resources to project tasks (activity, test session, meeting).

Consists to assign a resource to a task in a specific function. The function allows to define the resource daily cost.

A resource assignment contains data about work on task (planned, real, left and reassessed work).

Basic, you cannot delete a resource assignment after the resource has entered actual work on the activity.

This assignment can be deleted by a profile including the option "Can delete items with real work" in the access rights menu in the specific acces.

Similarly, if the resource has completed its activity, deletion is not possible. 

You keep track of the resources that have been assigned and worked on the activity.
    
.. note::

   Only resources allocated by the project can be assigned to project tasks.

   Assignment can be done in :ref:`activity`, :ref:`test-session` and :ref:`meeting` screens.








.. raw:: latex

    \newpage

.. index:: Organization

.. _organization-concept:

Organization
************

The notion of organization introduces a way to consolidate projects on a different hiererchic structure, apart from projects / sub-projects structure.

.. rubric:: Definition of the structure of the company in the frame of organizations 

Departments, Units, Location, ...

The organization summarizes the data of the projects in progress for the organization.

.. figure:: /images/GUI/CONCEPT_ORG_Organisations.png
   :align: center
   
   distribution within organizations

.. rubric:: Organization link

Each project can be linked to an organization.
     
Resources can be linked to an organization.

.. note::

   Depending on the profile, you can limit the visibility of resources to people in the same organization or team as the current user.

   Sub-projects are by default attached to the same organization as the parent, but can be integrated into another organization.


.. raw:: latex

    \newpage
    
.. index:: product 

.. _product-concept:

Product
*******

A product is a material object or for IT/IS projects is a software application.

.. rubric:: Composition of product

A product can have a complex structure that can be composed of sub-product and components.

A product and its components can have several versions that represent each declination.

See: :ref:`product-structure`

.. rubric:: Linked to a project

A product is an element delivered by a project.

The link with the project have no impact on project planning.

Indicates only that project is devoted to a specific product versions.

The link management is done in :ref:`planningelem_project` and :ref:`product-version` screens.

.. figure:: /images/GUI/CONCEPT_SCHEMA_LinkProductToProject.png
   :alt: Link with projects
   :align: center

   Link with projects

.. rubric:: Identifying the version that is the subject of treatment

Product (component) versions can be identified in these elements:

* :ref:`activity`
* :ref:`milestone`
* :ref:`requirement`
* :ref:`test-case`
* :ref:`test-session` 
* :ref:`ticket`.
  
The purpose is to identify which product (component) and its version that is the subject of the treatment.

Depending on the element, it has the possibility identifying version of  origin, target version or both.

.. rubric:: Document management

Documents can be identified to products.

See: :ref:`document`

.. rubric:: Management of product and component elements 

See: :ref:`ConfigurationManagement`, for detail about management of product and component elements.

.. _product-structure:

Product structure
-----------------

The product structure is defined depending on the relationships defined between product and component elements.

The rules defining a product structure are:

 .. compound:: **Relationships between product elements**

    * A product can have several sub-products.
    * A sub-product can be in the composition only one product.

    .. figure:: /images/GUI/CONCEPT_SCHEMA_LinkProductSubProduct.png
       :alt: Relationships between product elements
       :align: center

       Relationships between product elements

 .. compound:: **Relationships between product and component elements**

    * A product can be composed of several components.
    * A component can be in the composition of several products.

    .. figure:: /images/GUI/CONCEPT_SCHEMA_LinkProductComponent.png
       :alt: Relationships between product and component elements
       :align: center

       Relationships between product and component elements

 .. compound:: **Relationships between component elements**

    Components can be linked between them (N to N relationships).

    .. figure:: /images/GUI/CONCEPT_SCHEMA_LinkBetweenComponent.png
       :alt: Relationships between component elements
       :align: center

       Relationships between component elements

.. rubric:: Versions of product and component elements

A product can have several versions that represent each declination of product.

A component can have several versions that represent each declination of the component.

Links can be defined between versions of products and components, but only with the elements defined in the product structure.


.. figure:: /images/GUI/CONCEPT_SCHEMA_LinkProductComponentVersion.png
   :alt: Link between versions of product and component
   :align: center

   Link between versions of product and component


.. raw:: latex

    \newpage

.. index:: Planning

.. _planning:

Planning
********

ProjeQtOr implements work-driven planning method.

Based upon on resource availability and their capacity.

.. rubric:: Resource availability

Resource availability is defined by calendars and project allocation period.

 .. compound:: **Resource calendar**

    * Each resource is attached to a calendar to define its working days.
    * Tasks assigned to the resource will be planned according to working days defined in the calendar.
    * More detail, see: :ref:`resource-calendar`

 .. compound:: **Project allocation period**

    * The resource can be allocated to several projects.
    * Possibility to define allocation period.
    * More detail, see: :ref:`resource-allocation-to-project`

.. rubric:: Resource capacity

Resource capacity is defined on daily base.

The scheduling tool does not exceed the daily resource capacity.

.. note:: Full Time Equivalent (FTE)
 
   * This indicator is defined for each resource.   
   * It allows to define the daily capacity.
   * More detail, see: :ref:`resource`

.. rubric:: Project allocation rate

The project allocation rate is used to resolve allocation conflicts between projects.

It allows to define resource availability for a project during a period.

Use with the resource capacity, it allows to define the project allocation capacity on a weekly base.

.. rubric:: Task assignation rate

The task assignation rate is used to keep some scheduling time for other tasks.

Use with the resource capacity, it allows to define the assignation capacity on a daily base.

Draft planning
--------------

Two methods can be used to create a draft planning.

.. rubric:: Use planning mode "fixed duration"

This planning mode is used to define fixed duration tasks. See: :ref:`planningMode`

Dependencies allow to define the execution order of tasks. See: :ref:`dependency-links`

You can define this planning mode as defaut in the Activities Types screen for some types of activities you'll use in draft plannings

.. rubric:: Use faked and team resource

The faked and team resource can be useful to get a first estimate of project cost and duration without involving the real resources.

Planning schedule is calculated using of the work-driven planning method.

Faked and team resources can be mixed in same draft planning.

     .. compound:: **Faked resources**

        * For instance, you want to define a Java developer resource. You can create a resource named "Java developer #1".
        * There are several levels of Java developer with different daily costs (beginner, intermediary and expert).
        * You can define for this resource the functions and average daily cost for each level. (See: :ref:`resource-function-cost`)
        * You assign this resource to tasks, to a specific function (level). (See: :ref:`assignment`)
        * Faked resource will be easily replaced with real resources when project becomes real, with allocation replacement feature |Switch|. 

     .. compound:: **Team resource**

        * A team resource is a resource whose daily capacity has been defined to represent capacity of a team (Capacity (FTE) > 1).
        * For instance, you needed to define four Java developers, but you don’t want to create a resource for each. You can *overload* the daily capacity of the resource (Example: Capacity FTE=4).
        * Using team resources is very easy but renders estimation of project duration as draft, not taking into account constraint of different resources such as possibly different skills or expertise level.
        * With team resources it is very easy to estimate planning with different number of members in the team : what if I include 5 Java develpers instead of 4 ? Just change capacity to 5 and re-calculate planning...      


.. raw:: latex

    \newpage
    
.. index:: Planning elements (Concept)

.. _planning-element:

Planning elements
-----------------

ProjeQtOr offers standard planning elements like Project, Activity and Milestone.

But also, it offers two more planning element: Test session and Meeting.

.. rubric:: Project

This planning element defines the project.

* It allows to specify information on the project sheet like the customer, bill contact, sponsor, manager and objectives.
* Documents, notes and attachments can be annexed.
* More detail: :ref:`planningelem_project`

   .. compound:: **Sub-project**
   
       Sub-project is used to split the project.
       
       The project can be split to correspond the organizational breakdown or something else.

     .. note::
     
      **Separation of duties**

        * A project can be split into multiple sub projects.
        * A project leader and team can be allocated to each sub-project. 
        * Project allocation allows to define data visibility and isolate sub-projects. See: :ref:`allocation-to-project`
        * A supervisor can follow-up the project in its totality. 

        .. figure:: /images/GUI/CONCEPT_SCHEMA_SeparationDuties.png
           :alt: Separation of duties
           :align: center

           Separation of duties

.. rubric:: Activity

This planning element can be a phase, a delivery, a task or any other activity.

An activity can grouped other activities or be a task.

More detail, see: :ref:`activity` screen

 .. compound:: **Grouping of activities**

    * An activity can be the parent of activities.
    * This allows to define the structure of phases and deliveries.
    * Dates, works and costs of activities (child) are summarized in the activity (parent).

 .. compound:: **Task**
 
    * An activity is a task when it's not a parent of activities.
    * A task is assigned to resources for to be performed.



.. rubric:: Test session

This planning element is a specialized activity aimed for tests.

A test session allows to define a set of test case that must be run.

A test session can grouped other test sessions or be a task.

More detail, see: :ref:`test-session` screen.

 .. compound:: **Grouping of test sessions**

    * A test session can be the parent of test sessions.
    * This allows to define the structure of test sessions.
    * Dates, works and costs of test sessions (child) are summarized in the test session (parent).

 .. compound:: **Task**

    * A test session is a task when it's not a parent of test sessions.
    * A task is assigned to resources for to be performed.

.. rubric:: Milestone

This planning element is a flag in the planning, to point out key dates.

May be a transition point between phases, deliveries.

ProjeQtOr offers two types of milestone floating and fixed.

More detail, see: :ref:`milestone` screen.

.. _concept_meeting:

.. rubric:: Meeting

This planning element acts like a fixed milestone, but it's a task.

Like a milestone, a meeting can be a transition point. 

But also, like a task because it's possible to assign resources and planned work.

More detail, see: :ref:`meeting` screen.

.. raw:: latex

    \newpage

.. _dependencies-role:

Role of an dependency
---------------------

Dependencies allow to define the execution order of tasks (sequential or concurrent).

All planning elements can be linked to others.

Dependencies can be managed in the Gantt chart and in screen of planning element.

.. note:: 

   **Global parameter "Apply strict mode for dependencies"**

   If the value is set to “Yes”, the planning element (successor) can't start the same day that the end date of planning element (predecessor). 

.. rubric:: Dependency types

* ProjeQtOr offers only the dependency (Finish to Start).
* This section explains what are they dependency types can be reproduced or not.

 .. compound:: |DependancySS| **Start to Start**

   To reproduce this dependency type, it's possible to add a milestone as prior of both tasks.

 .. compound:: |DependancyES| **Start to Finish** 

   This dependency type can't be reproduced in ProjeQtOr.
       
   This is a very weird scenario.

 .. compound:: |DependancyEE| **Finish to Finish**

   The successor should not end after the end of the predecessor, which leads to planning "as late as possible". 
    
   Anyway, the successor can end before the predecessor. Note that the successor "should" not end after the end of predecessor, but in some cases this will not be respected:
    
   * if the resource is already 100% used until the end of the successor
   * if the successor has another predecessor of type "End-Start" or "Start-Start" and the remaining time is not enough to complete the task
   * if the delay from the planning start date does not allow to complete the task.

.. rubric:: Delay (days)

A delay can be defined between predecessor and successor (start).








.. _planningMode:

Planning mode
-------------

Planning mode allows to define constraints on planning elements: activity, test session and milestone.

.. rubric:: Milestones planning mode

Planning modes are grouped under two types for milestone :

 .. compound:: Floating

   * These planning modes have no constraint date.
   * Planning element is floating depending on its predecessors.
   * Planning modes: As soon as possible, Work together, Fixed duration and floating milestone.


 .. compound:: Fixed
   
   * These planning modes have constraint date.
   * Planning modes: Must not start before validated date, As late as possible, Regular and fixed milestone.

.. seealso:: 

   :ref:`Activity and Test session planning modes<progress-section-planning-mode>` and :ref:`Milestone planning modes<planning-mode-milestone>`.


.. _planning-mode-concept:

.. rubric:: Planning element planning mode

Several planning modes for your project elements are proposed to best manage the time spent on certain planning elements.

See: :ref:`planning-mode-gantt`

* As soon as possible
* Work together
* Fixed duration
* Must not start before validated start date
* Should end before validated end date
* Regular between dates
* Regular in full days
* Regular in half days
* Regular in quarter days
* Recurry (on weekly basis)
* Manual planning

 .. compound:: Prioritized planning elements

   Planning elements are scheduled in this order of priority:
   
   #. Manual planning
   #. Fixed date (Fixed milestone, Meeting)
   #. Recurrent activities - Planning modes "Regular..." (Activity, Test session)
   #. Fixed duration (Activity, Test session)
   #. Others

.. note:: 

   Since ProjeQtOr does not plan in advance, the "As fast as possible" planning mode without constraint date (floating point) is not available.


   **Default planning mode**

   Possibility to define the default planning mode according to element type.
   
   See: :ref:`activity-type`, :ref:`milestone-type` and :ref:`test-session-type` screens. 
   
   
   
   

.. _scheduling-priority:

Planning priority
-----------------

The planning priority allows to define planning order among planning elements.

Possible values: from 1 (highest priority) to 999 (lowest priority).

planning priority value is set in progress section of planning element.

.. note::

   If projects have different priorities, all elements of project with highest priority are scheduled first.


Project structure
-----------------

Work breakdown structure (WBS) is used to define project structure.

Breakdown can be done with sub-projects, activities and test sessions.

.. rubric:: Structure management

* As seen previously, the project can be split in subprojects.
* All other planning elements concerned by the project or subproject are put under them without structure.
* Planning elements can be grouped and orderly in hierarchical form.
* Structure management can be done in the Gantt chart or in planning elements screen.

.. rubric:: WBS element numbering

* The project is numbered by its id number.
* All other elements are numbered depending on their level and sequence.
* WBS numbering is automatically adjusted.

Project planning calculation
----------------------------

The project planning is calculated on the full project plan that includes parents and predecessor elements (dependencies).

.. rubric:: Scheduling

The calculation is executed task by task in the following order:

 #. Dependencies (Predecessor tasks are calculated first)
 #. Prioritized planning elements 
 #. Project priority
 #. Task priority
 #. Project structure (WBS)


.. rubric:: Constraints

The remaining work (left) on tasks will be distributed on the following days from starting planning date, taking into account several constraints:

* Resource availability
* Resource capacity
    
  * Project allocation capacity (Project allocation rate)
  * Assignation capacity (Task assignation rate)

* Planning mode


.. rubric:: Resource overloads

* This is not possible to overloading the resources. 
* The planning calculation process respects availability and capacity of the resource. 
* If it is not possible to distribute remaining work, on already planned days, the calculation process uses new available time slot.

.. raw:: latex

    \newpage

.. _projeqtor-roles:

ProjeQtOr roles
***************

A stakeholder can play many roles in ProjeQtOr.

Roles depends on :ref:`user-ress-contact-demystify`.

Specific roles are defined to allow:

* To categorize the stakeholders involved in the projects.
* To identify the stakeholders on items.
* To regroup the stakeholders to facilitate information broadcasting.


.. rubric:: Use to

* In items of elements.
* As reports parameters.
* As recipients list to mailing and alert.




.. raw:: latex

    \newpage


.. index:: Profile (Definition)

.. _profiles-definition:

Profiles definition
*******************

The profile is a group used to define application authorization and access rights to the data.

A user linked to a profile belongs to this group who share same application behavior.

.. note::

   You can define profiles to be conformed to the roles defined in your organization.
   
   Access rights management is done on :ref:`Acces Right<profiles>` screens 


.. rubric:: Used for

The profile is used to define access rights to application and data, first.

Also, the profile is used to send message, email and alert to groups.

.. rubric:: Selected profile in project allocation

A profile can be selected to a user, resource or contact in project allocation.

The profile selected is used to give data access to elements of the projects.

.. rubric:: Workflow definition

The profile is used to define who can change from one status to another one.

You can restrict or allow the state transition to another one according to the profile.

Workflow definition is managed in :ref:`workflow` screen.

.. rubric:: Predefined profiles

ProjeQtOr offer some predefined profiles.

 .. glossary::

    Administrator profile

     * This profile group all administrator users. 
     * Only these users can manage the application and see all data without restriction.
     * The user "admin" is already defined.

    Supervisor profile

     * Users linked to this profile have a visibility over all the projects.
     * This profile allows to monitor projects.

    Project leader profile

     * Users of this profile are the project leaders.
     * The project leader has a complete access to owns projects.
  
    Project member profile

     * A project member is working on projects allocated to it.
     * The user linked to this profile is a  member of  team projects.

    Project guest profile

     * Users linked to this profile have limited visibility to projects allocated to them.
     * The user "guest" is already defined.

.. rubric:: Predefined profiles (External)

ProjeQtOr allow to involve client employees in their projects.

The distinction between this profile and its equivalent, user access is more limited.

.. raw:: latex

    \newpage

.. _user-ress-contact-demystify:

Stakeholder definition
**********************

ProjeQtOr makes it possible to define the roles of the stakeholders.

The definition of stakeholders is done in part with the profile. This allows certain access and visibility rights to be determined.
See: profiles

and with the definition of user / resource / contact.

These combinations are used to define:

* Connection to the application.
* Data visibility.
* Availability.
* Roles.

These stakeholders can be either resource, contact, or users, but they can also be all three.

The next matrix shows the different possibilities.

.. list-table:: U=User - R=Resource - C=Contact
   :header-rows: 1
   :stub-columns: 1

   * - 
     - Connection
     - Visibility
     - Availability
   * - URC
     - |yes|
     - |yes|
     - |yes|
   * - UR
     - |yes|
     - |yes|
     - |yes|
   * - UC
     - |yes|
     - |yes|
     - |no|
   * - U
     - |yes|
     - |yes|
     - |no|
   * - R
     - |no|
     - |no|
     - |yes|
   














.. rubric:: Data visibility

.. figure:: /images/GUI/CONCEPT_SCHEMA_Stakeholder-DataVisibility.png
   :alt: Stakeholder data visibility
   :align: center

   Data visibility diagram

|

 .. compound:: **User profile**

    * To a user, data visibility is based on its user profile.
    * User profile defined general access to application functionalities and data.
    * Base access rights defined if a user has access to own projects or over all projects.

 .. compound:: **All projects**

    * This access right is typically reserved for administrators and supervisors. 
    * Users have access to all elements of all projects.

 .. compound:: **Own projects**
    
    * Users with this access right must be allocated to project to get data visibility.
    * Selected profile in allocation allows to define access rights on project elements.
    * For more detail, see: :ref:`allocation-to-project`.


.. raw:: latex

    \newpage


.. rubric:: Resource availability


.. figure:: /images/GUI/CONCEPT_SCHEMA_Stakeholder-ResourceAvailability.png
   :alt: Stakeholder resource availability
   :align: center

   Resource availability diagram

Only resource can be assigned to project activities.

Project allocation allows to define the resource availability on project.

 .. compound:: **Human resource**

    * Human resource is a project member.
    * Combined with a user, a human resource can connect to the application.

 .. compound:: **Material resource**

    * Material resources availability can be defined on projects.
    * But,  material resource must not  be  connected to the application.
    


.. rubric:: Contact roles  

 
* ProjeQtOr allows to involve contacts in projects.
* Combined with a user, a contact can connect to the application
* Combined with a resource, contact availability can be planned in projects.

.. figure:: /images/GUI/CONCEPT_SCHEMA_Stakeholder-ContactRoles.png
   :alt: Stakeholder contact roles
   :align: center

   Contact roles diagram


.. raw:: latex

    \newpage

Shared data
-----------

For a stakeholder, data on user, resource and contact are shared.

Allocation to project and user profile are also shared.

.. note::

   For a stakeholder, you can define and redefine the combination without losing data.


.. raw:: latex

    \newpage


.. index:: Resource (Function & Cost)

.. _resource-function-cost:

Resource function and cost
**************************

.. rubric:: Function

The function defines the generic competency of a resource.

It is used to define the role play by the resource on tasks.

In real work allocation screen, the function name will be displayed in the real work entry.

A main function must be defined to resource and it is used as default function.

A daily cost can be defined for each function of the resource.

The :ref:`function` screen allows to manage function list.

.. rubric:: Resource cost definition

Allows to define the daily cost, according to the functions of the resource. 

The daily cost is defined for a specific period.

.. rubric:: Real cost calculation

When real work is entered, the real cost is calculated with work of the day and daily cost for this period. 

.. rubric:: Planned cost calculation

When the project planning is calculated, resource cost is used to calculate planned cost.

Planned cost is calculated with planned work of the day and current daily cost. 

.. note::
 
   Function and cost are defined in :ref:`resource` screen.

.. raw:: latex

    \newpage

.. index:: Resource (Calendar) 

.. _resource-calendar:

Resource calendar
*****************

A calendar defines the working days in a the year.

A calendar is defined for a type of resources and each resource is attached to a calendar.

.. rubric:: Planning process

Calendars are used in the planning process which dispatches work on every working day. 

During the planning process, the assigned work to a resource is planned in its working days.

 .. note:: 

    You must re-calculate an existing planning to take into account changes on the calendar.

.. rubric:: Shows the availability of resources

Working days defined in a calendar allows to show availability of resources.

.. rubric:: Default calendar

The default calendar is used to define the working days in the year.

By default, this calendar is defined for all resources.

.. rubric:: Specific calendar

A specific calendar can be created to define working days for a type of resource.

.. note::

   A calendar is set in :ref:`resource` screen. 
   
   The calendar is defined in :ref:`calendars` screen.

.. rubric:: Use case

.. topic:: Public holiday

   You can use the default calendar to set public holidays.

.. topic:: Work schedule

   You can define a different work schedule to some resources.
   
   This calendar defined exceptions to normal working days.
   
   For instance, you can define a calendar for resources on leave on Wednesdays.

.. important:: **Personal calendar**

   Even if you can create a specific calendar to each resource, this is not the advised way to manage personal days off and vacations.
   
   You’d better use Administrative projects (enter real work in advance).

   
   
.. raw:: latex

    \newpage

.. title:: Contexts

.. index:: Context
.. index:: Ticket (Context)
.. index:: Test case (Environment)  

.. _context:

Contexts
********

The contexts defines a list of elements selectable to define ticket context and test case environment.

Contexts are initially set to be able to define contexts for IT Projects, for three context types :

* Environment

* Operating System

* Browser

They can be changed to be adapted to any kind of project.

.. rubric:: Section Description

.. tabularcolumns:: |l|l|

.. list-table::
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the context.
   * - Context type
     - One of the three context type.
   * - **Name**
     - Name of the context.
   * - Sort order
     - Number to define the order of display in lists
   * - :term:`Closed`
     - Flag to indicate that the context is archived.
 

.. topic:: Fields **Context type**

   The list is fixed. 
   
   Captions are translated and so can be changed in language file.

   
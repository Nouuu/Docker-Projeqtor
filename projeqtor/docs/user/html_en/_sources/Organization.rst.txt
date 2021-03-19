.. include:: ImageReplacement.txt

.. title:: Organizations

.. index:: Organizations

.. _Organizations:

Organizations
*************


.. rubric:: Management of organizations

* Management of organizations allows to edit the structure of the company in the frame of organizations (Departments, Units, Location, ...)
* The organization summarizes the data of the projects in progress for the organization.

Depending on the profile, you can limit the visibility of resources to people in the same organization or team as the current user.


.. figure:: /images/GUI/ORGANIZATION_SCR_Globalview.png
   :alt: Organization global view
   
   Organization global view
   
     
   
.. rubric:: Section Description


.. sidebar:: Other sections

   * :ref:`Current project<progress-section>`
   * :ref:`Linked element<linkElement-section>`   
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>`
   * :ref:`organization-concept`
   
   
.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the Organization.
   * - |RequiredField| Name
     - Short name of the Organization.
   * - |RequiredField| Organization type
     - Type of organization.
   * - Manager
     - Manager of organization.
   * - Hierarchy
     - list of parents organizations.
   * - Parent organization
     - parent organization.
   * - Display the structure
     - Displays the structure of the selected organization in a popup.  
   * - Show idle organization
     - Show closed organizations in the structure display.  
   * - Closed 
     - Box checked indicates the organization is archived.
   * - Closed dates
     - Displays the closing date and time of the organization.  
   * - Description
     - Description of the organization 

 
 
.. rubric:: Display the structure
 
Click on the button to open a pop-up which will display in a more graphic way the structure of the organization you have selected.

.. figure:: /images/GUI/ORGANIZATION_SCR_structure-display.png
   :alt: structure display
   
   Structure display
   
   
   
   
.. rubric:: Project Synthesis


.. figure:: /images/GUI/ORGANIZATION_SCR_ProjectSynthesis.PNG
   :alt: Project synthesis
   
   Project financial synthesis
   
This section displays a summary of the costs recorded on the projects related to the selected organization.


.. rubric:: Projects of organization and its sub-organizations


.. figure:: /images/GUI/ORGANIZATION_SCR_ProjectOrganization.png
   :alt: list of projects and its sub-projects
   
   list of projects and its sub-projects
   
In this section you will find the list of projects and sub-projects linked to the selected organization.


.. _linked-resource-organization:

.. rubric:: Linked resources

This section allows you to see the resources attached to the selected organization.   
      
* Click on |Add| to add a new resource in the organization

* Click on |Delete| to delete a resource of the organization


* Click the :kbd:`allocate all organization members to a project` button to assign all of an organization's resources to a project.
* The project assignment pop-up opens and allows you to choose your resources.
   
   
See: :ref:`allocation-section`   


.. rubric:: Linked elements

This section allow you to linked any element of ProjeQtOr to the selected organization.

See: :ref:`linkelement-section`


.. rubric:: Attachments

This section allows you to attach elements external to the selected organization. Whether documents or url addresses.

See: :ref:`attachment-section` 


.. rubric:: Notes

This section allows you to add notes on elements linked to the selected organization

See: :ref:`note-section`    
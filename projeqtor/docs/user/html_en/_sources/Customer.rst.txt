.. include:: ImageReplacement.txt

.. raw:: latex

    \newpage

.. title:: Clients & Contacts

.. index:: Client

.. _customer:

Clients
-------

The client is the entity for which the project is set.

It is generally the owner of the project, and in many cases it is the payer.

It can be an internal entity, into the same enterprise, or a different enterprise, or the entity of an enterprise.

The client defined here is not a person. Real persons into a client entity are called “Contacts”. 

.. figure:: /images/GUI/CUSTOMER_SCR_Clients.png
   :alt: Clients screen
   
   Clients screen

.. rubric:: Section Description

.. sidebar:: Other sections
  
   * :ref:`Attachments<attachment-section>`   
   * :ref:`Notes<note-section>`   

.. tabularcolumns:: |l|l|

.. list-table:: Required field |ReqFieldLegend|
   :widths: 20, 80
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the client.
   * - |RequiredField| client name
     - Short name of the client.
   * - |RequiredField| Type of client
     - Type of client.
   * - Client code
     - Code of the client.
   * - Payment deadline
     - The payment deadline is stated on the bill for this client.
   * - Tax
     - Tax rates that are applied to bill amounts for this client.
   * - Tax number
     - Tax reference number, to be displayed on the bill. 
   * - :term:`Closed`
     - Flag to indicate that the client is archived.
   * - :term:`Description`
     - Complete description of the client.

.. rubric:: Address section

Full address of the client.

.. rubric:: Projects section

List of projects related to the client.

.. rubric:: Contacts section

Displays the names of client-related contacts. 

.. figure:: /images/GUI/CUSTOMER_ZONE_Contacts.png
   :alt: Contacts section
   
   Contacts section
   
   
You can create the contacts directly on the contact screen.

But you can create the contacts directly into the contact section

* Click on |Add| to create the contact
* Click on |Delete| to delete the contact

When you want to add a contact, the window with the existing list of clients is displayed.

You can select or create a contact from this window, the information will be reflected directly in the contact screen.

   
   
.. rubric:: Client quotation, client commands list and client bill list 

These sections allow you to have a summary of the various financial documents concerning the client selected.

You find the list of quotes, orders and invoices of this client in tables for easy reading

.. figure:: /images/GUI/CUSTOMER_ZONE_Financial.png
   :alt: financial monitoring sections
   
   Financial monitoring sections



.. rubric:: List of tickets

This section allows you to see all open tickets for the selected client.


.. figure:: /images/GUI/CUSTOMER_ZONE_Tickets.png
   :alt: List of tickets for this client
   
   List of tickets for this client
   
.. note:: To display these sections, you must set the options "list quotes, commands and bills on client form" on yes in the global parameters but also in the user parameters.   

   See: :ref:`Global Parameters<display>`
   See: :ref:`Users Parameters<display-parameters>`  


.. raw:: latex

    \newpage

.. index:: Contact (Screen)
.. index:: Client (Contact) 

.. _contact:

Contacts
--------

.. figure:: /images/GUI/CUSTOMER_SCR_Contacts.png
   :alt: Contacts screen
   :align: center
   
   Contacts screen

.. sidebar:: Other 

   * :ref:`projeqtor-roles`
   * :ref:`profiles-definition`
   * :ref:`user-ress-contact-demystify`
   * :ref:`photo`
   * :ref:`Allocations<allocation-section>`
      
A contact is a person in a business relationship with the company.

The company keeps all information data to be able to contact him when needed.

A contact can be a person in the client organization.

A contact can be the contact person for contracts, sales and billing.


   

.. topic:: Field Is a resource
   
   * Check this if the contact must also be a resource.
   * The contact will then also appear in the “Resources” list. 

.. topic:: Field Is a user

   * Check this if the contact must connect to the application. 
   * You must then define the **User name** and **Profile** fields.
   * The contact will then also appear in the “Users” list. 

See: :ref:`Ressource Contact User<user-ress-contact-demystify>`



.. rubric:: Section Allocations to project

Allows to allocate your contact to a project

see: :ref:`Allocations<allocation-section>`

.. rubric:: Section List of subscription for this contact

You can see the items followed by your contact in this section

.. figure:: /images/GUI/CUSTOMER_ZONE_Subscription.png
   :alt: list of elements followed by your contact
   :align: center
   
   list of elements followed by your contact
   
.. rubric:: Section Miscellanous

if the box is checked, the contact will not receive the mails sent to the team

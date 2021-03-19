.. include:: ImageReplacement.txt

.. title:: Asset Management

.. index:: Asset management

.. _asset-management:

Asset Management
----------------

This module is dedicated to the management of your IT infrastructure.

You can manage:

* All types of equipment
* Equipment categories
* Brands
* Models
* Equipement localisations


Equipment
=========

This screen allows you to manage lists of licenses, versions, products or even components linked to equipment.

* Define the list of devices contained in another device.

* Display the global tree of the equipment constituting an equipment, by being able to close or expand a given level.

* When copying a device you can select the complete composition of this device.

* Each piece of equipment contained is duplicated, recursively, by initializing the unique data (serial number, references, etc.). 

.. figure:: /images/GUI/ASSETMANAGMENT_SCR_Assets.png
   :alt: Asset management screen
   
   Asset management screen


.. note:: 

   Each asset related to a resource or an user, is displayed on the Resource screen and the user screen
   
   See: :ref:`Resource<allocated-asset-resource>`
   
   See: :ref:`User<allocated-asset-user>`

.. rubric:: Description

This section allows you to enter the details of the composition of an item of equipment.
 
.. list-table:: Fields of add an origin element dialog box
   :header-rows: 1

   * - Field
     - Description
   * - name
     - Name of the asset.
   * - Asset type
     - Type of the asset defined in the asset type screen.
   * - Brand
     - Brand of the asset defined in the brand screen
   * - Model 
     - Model of the brand of the asset.
   * - Provider  
     - Equipment provider
   * - Asset category
     - Category of the asset. Individual or collective by defaut.
   * - Parent asset
     - Link to parent equipment. This contains the other equipment. A computer has multiple devices. 
   * - Serial number
     - Serial number of the material or the serial numer of a licence.
   * - Inventory number
     - Add your own identification number to your equipment inventory.
   * - Description
     - Description of the asset
     
     
     
.. rubric:: Attribution section


.. figure:: /images/GUI/ASSETMANAGMENT_ZONE_Attribution.png
   :alt: Attribution section
   
   Attribution section
   
   
This section allow to define:

* A status for each device according to the selected workflow.

* An installation date and a possible decommissioning date.

* The location of the equipment, with the possibility of defining a list (see: :ref:`asset-types`) and / or a manual entry field for more precision.

* The user who will benefit from this equipment.

* The closed check box. Which allows to put the equipment in archive mode.     

.. rubric:: Costs section

This section allow to define costs for the selected asset.

You can set a cost for:

* Purchase value
* The warranty period
* End of warranty date
* The amortization period
* The need for insurance

.. figure:: /images/GUI/ASSETMANAGMENT_ZONE_Costs.png
   :alt: Costs section
   
   Costs section
   
   

.. rubric:: Asset composition section

When you define an element parent, the components of the element appear in this section giving you the complete structure of an element.

.. figure:: /images/GUI/ASSETMANAGMENT_ZONE_AssetComposition.png
   :alt: Asset composition 
   
   Asset composition 
   
   
   
The button :kbd:`display the structure` opens a pop up which summarizes the complete composition of your equipment in table form.

You can print this box.


.. figure:: /images/GUI/ASSETMANAGMENT_ZONE_DisplayStructure.png
   :alt: Asset composition section
   
   Asset composition section


   
   

.. _asset-types:

Asset types
===========

The types of asset in equipment make it possible to list the different materials of an equipment.

For example, a workstation contains a computer, peripherals such as a screen, a mouse, a keyboard, or even a webcam, software, licenses, a printer ...

But you can also create even more detailed lists with types of information storage, processing, or network equipment.

.. sidebar:: Other sections

   * :ref:`Behavior <behavior-section>`

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the type.
   * - |RequiredField| Name
     - Name of the type.
   * - Code
     - Code of the type.
   * - |RequiredField| Workflow
     - Defined the workflow ruling status change for items of this type (see: :ref:`workflow`).
   * - Sort order
     - Number to define order of display in lists.
   * - :term:`Closed`
     - Box checked indicates the type is archived.
   * - Description
     - Description of the type.


You can define an icon for each type of asset.  

ProjeQtOr puts some icons at your disposal but you can create and import yours in the application.
   
Save your icons in the **www\\projeqtor\\view\\icons** folder and relaunch the application.    
   
   
.. _asset-category:

Asset category
==============

The screen of the equipment categories will allow you to make a more detailed inventory of certain equipment.

You can determine for example whether a piece of equipment can be personal, for a service or collective.

But you can also determine if a device is part of a hardware, network or workstation architecture


.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the type.
   * - |RequiredField| Name
     - Name of the type.
   * - Sort order
     - Number to define order of display in lists.
   * - :term:`Closed`
     - Box checked indicates the type is archived.



.. _brands:

Brands
======

The brand screen allows to create a list of brands making up your IT infrastructure.

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the type.
   * - |RequiredField| Name
     - Name of the type.
   * - :term:`Closed`
     - Box checked indicates the type is archived.

.. _models:

Models
======

The model screen allows you to create a list of models linked to a brand and type of equipment.

.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the type.
   * - |RequiredField| brand
     - Name of the brand.
   * - Name
     - Name of the model 
   * - Asset type
     - Name of the asset type    
   * - Description
     - Description of the model.

.. _location:

Location
========

Location screen allow to create a list of places so that you can locate your equipment.


.. figure:: /images/GUI/ASSETMANAGMENT_SCR_Location.png
   :alt: Location screen
   
   Location screen
   

.. rubric:: Description section
   
.. tabularcolumns:: |l|l|

.. list-table:: Required fields |ReqFieldLegend|
   :header-rows: 1

   * - Field
     - Description
   * - :term:`Id`
     - Unique Id for the type.
   * - |RequiredField| Name
     - Name of the location.
   * - Sort order
     - Number to define order of display in lists. 
   * - :term:`Closed`
     - Box checked indicates the type is archived.
   * - Description
     - Description of the location
     
The "location" fields in the assets screen offers the possibility of selecting from the recorded list of locations and a manual entry field allowing you to add details with alphanumeric characters.



.. rubric:: Adress section


You can complete the axacte address by filling in numerous fields : 
   
* Street 
* Complement
* Zip code
* City
* State
* Country

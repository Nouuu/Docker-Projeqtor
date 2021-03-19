<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : antonio
 * 
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/**
 * ===========================================================================**********
 * Abstract class defining all methods to interact with database,
 * using Sql class.
 * Give public visibility to elementary methods (save, delete, copy, ...)
 * and constructor.
 */
if (file_exists ( '../_securityCheck.php' ))
  include_once ('../_securityCheck.php');

abstract class SqlElement {
  // List of fields that will be exposed in general user interface
  public $id;
  // every SqlElement have an id !!!
  public static $_evaluationString = '###EVAL###';

  public static $_evaluationStringForbiddenKeywords = array(
      'paramDb', 
      'getGlobalParameter', 
      'readfile', 
      'exit', 
      'fopen', 
      'fsockopen', 
      'copy', 
      'move', 
      'kill', 
      'file_get_contents', 
      'include', 
      'stream_context_create');

  private static $_copyInProgress = false;
  
  private static $staticCostVisibility = null;

  private static $staticWorkVisibility = null;

  private static $staticDeleteConfirmed = false;

  private static $staticSaveConfirmed = false;
  
  // Store the layout of the different object classes
  private static $_tablesFormatList = array();
  
  // Define the layout that will be used for lists
  private static $_layout = '
    <th field="id" formatter="numericFormatter" width="10%"># ${id}</th>
    <th field="name" width="85%">${name}</th> 
    <th field="idle" width="5%" formatter="booleanFormatter">${idle}</th>
    ';
  
  // Define the specific field attributes
  private static $_fieldsAttributes = array("name" => "required");

// BEGIN - ADD BY TABARY - TOOLTIP
  private static $_fieldsTooltip = array();
// END - ADD BY TABARY - TOOLTIP    
  
  private static $_defaultValues = array();
  
  public static $_doNotSaveLastUpdateDateTime=false;
  
  // Management of cache for queries : cache is only valid during current script
  public static $_cachedQuery = array('Habilitation' => array(), 'Menu' => array(), 'PluginTriggeredEvent' => array(), 'Plugin' => array(), 'RestrictList'=>array());
  
  // Management of extraHiddenFileds per type, status or profile
  private static $_extraHiddenFields = null;
  
  // Management of extraReadonlyFileds per type, status or profile
  private static $_extraReadonlyFields = null;
  
  // Management of extraRequiredFileds per type, status or profile
  private static $_extraRequiredFields = null;
  
  // ADD BY Marc TABARY - 2017-03-02 - DRAW SPINNER
  // Management of drawing spinner for a field
  // Array :
  // - key : Name of the field
  // - value : Attributes of spinner.
  // For each attribute : String separated by commas : 'attribute of spinner:value,'
  // Attributes are :
  // - min : Minimal value of spinner (default = 0)
  // - max : Maximal value of spinner (default = 100)
  // - step : Step of increment (default = 1)
  // - bkColor : Span on right with this background color
  // Sample : $_spinnersAttributes = array(
  // "year"=>"min:2000,max:2100,step:1"
  // );
  private static $_spinnersAttributes = array();
  // END ADD BY Marc TABARY - 2017-03-02 - DRAW SPINNER
  
  // ADD BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
  // Fields list that must be disabled when something changes on form detail
  // Avalable for fields :
  // - in widget (dijit)
  // - specific ('_spe_') that have an id in DOM that respect the following naming rule :
  // id_[name of the field without _spe_] ex : field with name _spe_theSpecificField must have this id in DOM id__theSpecificField
  // If the specific field has in it name 'button' (case insensitive) then it will be hidden else it will be readonly
  // getStaticDisabledFieldOnChange et getDisabledFieldOnChange must be implemented on this class
  // See implementation in OrganizationBudgetElementMain
  private static $_disabledFieldsOnChange = array();
  // END ADD BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
  
  // All dependencies between objects :
  // control => sub-object must not exist to allow deletion
  // cascade => sub-objects are automaticaly deleted
  // confirm => confirmation will be requested
  private static $_relationShip = array(
      "AccessProfile" => array(
          "AccessRight" => "controlStrict"), 
      "AccessProfileNoProject" => array(
          "AccessRight" => "controlStrict"),
      "AccessScope" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"),
      "AccessScopeNoProject" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"),
      "AccessScopeRead" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"), 
      "AccessScopeCreate" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"), 
      "AccessScopeUpdate" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"), 
      "AccessScopeDelete" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"),
      "AccessScopeNoProjectRead" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"), 
      "AccessScopeNoProjectCreate" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"), 
      "AccessScopeNoProjectUpdate" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"), 
      "AccessScopeNoProjectDelete" => array(
          "AccessProfile" => "controlStrict",
          "AccessProfileNoProject"=> "controlStrict"),
      "Asset" => array("Location" => "controlStrict",
                       "ProductAsset"=>"cascade"),
      "AssetCategory" => array("Asset" => "controlStrict"),
      "Assignment" => array(
          "AssignmentRecurring"=>"cascade",
          "PlannedWork" => "cascade", 
          "Work" => "controlStrict"), 
      "Action" => array(
          "Attachment" => "cascade", 
          "Link" => "cascade", 
          "Note" => "cascade"), 
      "ActionType" => array(
          "Action" => "controlStrict"), 
      "Activity" => array(
          "Activity" => "confirm", 
          "Assignment" => "confirm", 
          "Attachment" => "cascade", 
          "Dependency" => "cascade", 
          "Link" => "cascade", 
          "Meeting"=>"confirm",
          "Milestone" => "confirm", 
          "Note" => "cascade", 
          "PeriodicMeeting"=>"confirm",
          "PlannedWork" => "cascade",
          "TestSession"=>"confirm", 
          "Ticket" => "control"
          ), 
      "ActivityType" => array(
          "Activity" => "controlStrict"), 
      "Bill" => array(
          "BillLine" => "confirm", 
          "Note" => "cascade",
          "Situation"=> "cascade"), 
      "BillType" => array(
          "Bill" => "controlStrict"), 
      "Brand" => array("Asset" => "controlStrict","Model" => "controlStrict"),
      "CallForTender" => array(
          "Tender" => "controlStrict", 
          "TenderEvaluationCriteria" => "cascade",
          "Situation"=> "cascade"), 
      "CallForTenderType" => array(
          "CallForTender" => "controlStrict"), 
      "CalendarDefinition" => array(
          "Calendar" => "cascade", 
          "Resource" => "controlStrict"),
      "CatalogUO" => array("Complexity" => "cascade","WorkUnit" => "cascade"),
      "Checklist" => array(
          "ChecklistLine" => "cascade"), 
      "ChecklistDefinition" => array(
          "Checklist" => "control", 
          "ChecklistDefinitionLine" => "cascade"), 
      "ClientType" => array(
          "Client" => "controlStrict"), 
      "Command" => array(
          "Attachment" => "cascade", 
          "Link" => "cascade", 
          "Note" => "cascade",
          "Situation"=> "cascade"), 
      "CommandType" => array(
          "Command" => "controlStrict"), 
      "Component" => array(
          "ProductStructure" => "cascade", 
          "ComponentVersion" => "control"), 
      "ComponentVersion" => array(
          "Requirement" => "control", 
          "TestCase" => "control", 
          "TestSession" => "control", 
          "Ticket" => "control",
          "ProductAsset"=>"cascade"), 
      "Complexity" => array("ComplexityValues" => "cascade"),
      "Contact" => array(
          "Activity" => "controlStrict", 
          "Affectation" => "confirm", 
          "Bill" => "controlStrict", 
          "Product" => "controlStrict", 
          "Project" => "controlStrict", 
          "Tender" => "ControlStrict", 
          "Ticket" => "controlStrict", 
          "Version" => "controlStrict"), 
      "ContextType" => array(
          "Context" => "controlStrict"), 
      "Client" => array(
          "Project" => "control"), 
      "Criticality" => array(
          "Issue" => "controlStrict", 
          "Opportunity" => "controlStrict", 
          "Requirement" => "controlStrict", 
          "Risk" => "controlStrict", 
          "Ticket" => "controlStrict"), 
      "Decision" => array(
          "Attachment" => "cascade", 
          "Link" => "cascade", 
          "Note" => "cascade"), 
      "DecisionType" => array(
          "Decision" => "controlStrict"), 
      "Document" => array(
          "Approver" => "control", 
          "DocumentVersion" => "control", 
          "Link" => "cascade", 
          "Note" => "cascade"), 
      "DocumentVersion" => array("Approver" => "cascade"), 
      "DocumentDirectory" => array("Document" => "control", "DocumentDirectory" => "control"), 
      "DocumentType" => array("Document" => "controlStrict"), 
      "Efficiency" => array("Action" => "controlStrict"), 
// MTY - LEAVE SYSTEM
      "Employee" => array(
          "EmployeeLeaveEarned" => "control",
          "Leave" => "control",
          "EmploymentContract" => "control",
          ),
      "EmploymentContractEndReason" => array(
          "EmploymentContract" => "controlStrict"
          ),
      "EmploymentContractType" => array(
          "EmploymentContract" => "controlStrict",
          "CustomEarnedRulesOfEmploymentContractType" => "control",
          "LeaveTypeOfEmploymentContractType" => "control"
          ),
// MTY - LEAVE SYSTEM
      "ExpenseDetailType" => array("ExpenseDetail" => "controlStrict"), 
      "Feasibility" => array("Requirement" => "controlStrict"), 
      "Filter" => array("FilterCriteria" => "cascade"), 
      "Health" => array("Project" => "controlStrict"), 
      "IndividualExpenseType" => array("IndividualExpense" => "controlStrict"), 
      "Issue" => array("Attachment" => "cascade", "Link" => "cascade", "Note" => "cascade"), 
      "IssueType" => array("Issue" => "controlStrict"), 
      "JoblistDefinition" => array("Job" => "control", "JobDefinition" => "cascade"), 
// MTY - LEAVE SYSTEM
      "Leave" => array(
          "work" => "cascade",
          "plannedWork" => "cascade"
          ),
      "LeaveType" => array(
          "Leave" => "confirm",
          "CustomEarnedRulesOfEmploymentContractType" => "confirm",
          "LeaveTypeOfEmploymentContractType" => "confirm",
//          "EmployeeLeaveEarned" => "confirm",
          ),
// MTY - LEAVE SYSTEM      
      "Likelihood" => array("Opportunity" => "controlStrict", "Risk" => "controlStrict"), 
      "Location" => array("Asset" => "controlStrict"),
      "Meeting" => array(
          "Assignment" => "cascade", 
          "Attachment" => "cascade", 
          "Dependency" => "cascade", 
          "Link" => "cascade", 
          "Note" => "cascade", 
          "PlannedWork" => "cascade"), 
      "MeetingType" => array("Meeting" => "controlStrict", "PeriodicMeeting" => "controlStrict"), 
      "Menu" => array("AccessRight" => "cascade"), 
      "MessageLegal" => array("MessageLegalFollowup" => "confirm"),
      "MessageType" => array("Message" => "controlStrict"), 
      "Milestone" => array("Attachment" => "cascade", "Dependency" => "cascade", "Link" => "cascade", "Note" => "cascade"), 
      "MilestoneType" => array("Milestone" => "controlStrict"),     
      "Model" => array("Brand" => "controlStrict","Asset" => "controlStrict"),
      "Notifiable" => array("NotificationDefinition" => "controlStrict",
                             "Notification" => "controlStrict"
                            ),
      "NotificationDefinition" => array("Notification" => "controlStrict"),
      "OverallProgress" => array("Project" => "controlStrict"), 
      "OpportunityType" => array("Opportunity" => "controlStrict"), 
      "Organization" => array("Attachment" => "cascade", "Link" => "cascade", "Note" => "cascade", 
          "BudgetElement" => "cascade", 
          "Project" => "controlStrict", "Organization" => "control", "Resource" => "controlStrict"), 
      "PaymentType" => array("Payment" => "controlStrict"),
      "PeriodicMeeting" => array("Assignment" => "cascade", "Meeting" => "cascade", "Note" => "cascade"), 
      "Priority" => array(
          "Action" => "controlStrict", 
          "Issue" => "controlStrict", 
          "Opportunity" => "controlStrict", 
          "Risk" => "controlStrict", 
          "TestCase" => "controlStrict", 
          "Ticket" => "controlStrict"), 
      "Profile" => array(
          "AccessRight" => "cascade", 
          "Habilitation" => "cascade", 
          "Message" => "cascade", 
          "Resource" => "controlStrict", 
          "User" => "controlStrict"), 
      "ProjectExpenseType" => array("ProjectExpense" => "controlStrict"), 
      "ProjectType" => array("Project" => "controlStrict"), 
      "Product" => array(
          "Component" => "cascade", 
          "Requirement" => "control", 
          "TestCase" => "control", 
          "TestSession" => "control", 
          "ProductVersion" => "control"), 
      "ProductVersion" => array(
          "Requirement" => "control", 
          "TestCase" => "control", 
          "TestSession" => "control", 
          "VersionProject" => "cascade", 
          "Ticket" => "control", 
          "Activity" => "control",
          "ProductAsset"=>"cascade"), 
      "Project" => array(
          "Action" => "control", 
          "Activity" => "confirm", 
          "Affectation" => "confirm", 
          "Assignment" => "cascade", 
          "Attachment" => "cascade", 
          "Bill" => "control", 
          "CallForTender" => "control", 
          "Command" => "control", 
          "Decision" => "control", 
          "Dependency" => "cascade", 
          "Document" => "control", 
      		"IndicatorDefinitionPerProject" => "cascade",
          "Issue" => "control", 
          "IndividualExpense" => "control", 
          "Link" => "cascade", 
          "Meeting" => "control", 
          "Message" => "cascade", 
          "Milestone" => "confirm", 
          "Note" => "cascade", 
          "Opportunity" => "control", 
          "Parameter" => "cascade", 
          "PeriodicMeeting" => "control",
          "PlannedWork" => "cascade", 
          "Project" => "confirm", 
          "ProjectExpense" => "control", 
          "ProjectSituation" => "cascade",
          "ProviderBill"=> "control",
          "ProviderOrder"=> "control",
          "Requirement" => "control", 
          "Risk" => "control", 
          "Question" => "control", 
          "Quotation" => "control", 
      		"StatusMailPerProject" => "cascade",
          "Tender" => "control",
          "Term" => "control", 
          "TestCase" => "control", 
          "TestSession" => "control", 
          "Ticket" => "control", 
      		"TicketDelayPerProject" => "cascade",
          "VersionProject" => "cascade", 
          "Work" => "controlStrict"), 
      "Provider" => array(
          "ProjectExpense" => "controlStrict", 
          "Tender" => "ControlStrict",
          "Situation"=> "cascade"), 
      "ProviderBill" => array("BillLine" => "cascade",
                              "ProviderTerm"=>"controlStrict",
                              "Situation"=> "cascade"),
      "ProviderOrder" => array("BillLine" => "cascade",
                               "ProviderTerm"=>"controlStrict",
                               "Situation"=> "cascade"),
      "ProviderTerm" => array("BillLine" => "cascade"),
      "Quality" => array("Project" => "controlStrict"), 
      "Question" => array("Link" => "cascade"), 
      "QuestionType" => array("Question" => "controlStrict"), 
      "Quotation" => array("Attachment" => "cascade", "Link" => "cascade", "Note" => "cascade", "Situation"=> "cascade"), 
      "QuotationType" => array("Quotation" => "controlStrict"), 
      "Recipient" => array("Bill" => "control", "Project" => "controlStrict"), 
      "RequirementType" => array("Requirement" => "controlStrict"), 
      "Requirement" => array("Attachment" => "cascade", "Link" => "cascade", "Note" => "cascade", "Requirement" => "control"), 
      "Resolution" => array("Ticket" => "controlStrict"), 
      "Resource" => array(
          "Action" => "controlStrict", 
          "Activity" => "controlStrict", 
          "Affectation" => "confirm", 
          "Assignment" => "control", 
          "CallForTender" => "controlStrict", 
          "Decision" => "controlStrict", 
          "Issue" => "controlStrict", 
          "Meeting" => "controlStrict", 
          "Milestone" => "controlStrict", 
          "Question" => "controlStrict", 
          "Requirement" => "controlStrict", 
          "ResourceCost" => "cascade", 
          "Risk" => "controlStrict", 
          "Ticket" => "controlStrict", 
          "Tender" => "controlStrict", 
          "TestCase" => "controlStrict", 
          "TestSession" => "controlStrict", 
          "Work" => "controlStrict"), 
      "ResourceTeam" => array ("ResourceTeamAffectation" => "cascade"),
      "Risk" => array("Attachment" => "cascade", "Link" => "cascade", "Note" => "cascade"), 
      "RiskLevel" => array("Requirement" => "controlStrict"), 
      "RiskType" => array("Risk" => "controlStrict"), 
      "Role" => array(
          "Affectation" => "controlStrict", 
          "Assignment" => "controlStrict", 
          "Resource" => "controlStrict", 
          "ResourceCost" => "controlStrict"), 
      "Severity" => array("Opportunity" => "controlStrict", "Risk" => "controlStrict"), 
      "Status" => array(
          "Action" => "controlStrict", 
          "Activity" => "controlStrict", 
          "Bill" => "controlStrict", 
          "Command" => "controlStrict", 
          "Decision" => "controlStrict", 
          "Document" => "controlStrict", 
          "DocumentVersion" => "controlStrict", 
          "Expense" => "controlStrict", 
          "IndividualExpense" => "controlStrict", 
          "Issue" => "controlStrict", 
// MTY - LEAVE SYSTEM          
          "Leave" => "controlStrict",
// MTY - LEAVE SYSTEM          
          "Mail" => "controlStrict", 
          "Meeting" => "controlStrict", 
          "Milestone" => "controlStrict", 
          "Opportunity" => "controlStrict", 
          "Project" => "controlStrict", 
          "ProjectExpense" => "controlStrict", 
          "Question" => "controlStrict", 
          "Quotation" => "controlStrict", 
          "Requirement" => "controlStrict", 
          "Risk" => "controlStrict", 
          "StatusMail" => "controlStrict", 
          "TestCase" => "controlStrict", 
          "TestSession" => "controlStrict", 
          "Ticket" => "controlStrict", 
          "WorkflowStatus" => "cascade"), 
      "Team" => array("Resource" => "control"), 
      "Tender" => array(
          "TenderEvaluation" => "cascade", 
          "Situation"=> "cascade"), 
      "TenderStatus" => array("Tender" => "control"), 
      "TenderType" => array("Tender" => "control"), 
      "Term" => array("Dependency" => "cascade"), 
      "TestCase" => array("TestCase" => "control", "TestCaseRun" => "control"), 
      "TestCaseType" => array("TestCase" => "controlStrict"), 
      "TestSession" => array(
          "Assignment" => "confirm", 
          "Attachment" => "cascade", 
          "Dependency" => "cascade", 
          "Link" => "cascade", 
          "Note" => "cascade", 
          "PlannedWork" => "cascade", 
          "TestCaseRun" => "cascade"), 
      "TestSessionType" => array("TestSession" => "controlStrict"), 
      "Ticket" => array(
          "Attachment" => "cascade", 
          "Link" => "cascade", 
          "Note" => "cascade", 
          "Ticket" => "control", 
          "Work" => "control"), 
      "TicketType" => array("Ticket" => "controlStrict"), 
      "Trend" => array("Project" => "controlStrict"), 
      "Urgency" => array(
          "Delay" => "controlStrict", 
          "Requirement" => "controlStrict", 
          "Ticket" => "controlStrict", 
          "TicketDelay" => "controlStrict"), 
      "User" => array(
          "Action" => "controlStrict", 
          "Activity" => "controlStrict", 
          "Affectation" => "confirm", 
          "Attachment" => "control", 
          "Command" => "controlStrict", 
          "Decision" => "controlStrict", 
          "Issue" => "controlStrict", 
          "Meeting" => "controlStrict", 
          "Message" => "control", 
          "Milestone" => "controlStrict", 
          "Note" => "control", 
          "Opportunity" => "controlStrict", 
          "Parameter" => "cascade", 
          "Project" => "controlStrict", 
          "Question" => "controlStrict", 
          "Quotation" => "controlStrict", 
          "Requirement" => "controlStrict", 
          "Risk" => "controlStrict", 
          "TestCase" => "controlStrict", 
          "TestSession" => "controlStrict", 
          "Ticket" => "controlStrict"), 
      "Version" => array(
          "Requirement" => "control", 
          "TestCase" => "control", 
          "TestSession" => "control", 
          "VersionProject" => "cascade"), 
      // "VersioningType" => array("Versioning" => "controlStrict"),
      "WorkElement" => array("Work" => "cascade"), 
      "WorkUnit" => array("ComplexityValues" => "cascade"),
      "Workflow" => array(
          "ActionType" => "controlStrict", 
          "ActivityType" => "controlStrict", 
          "BillType" => "controlStrict", 
          "ClientType" => "controlStrict", 
          "CommandType" => "controlStrict", 
          // "ContextType"=>"controlStrict",
          "DecisionType" => "controlStrict", 
          "DocumentType" => "controlStrict", 
          // "ExpenseDetailType"=>"controlStrict",
          "IndividualExpenseType" => "controlStrict", 
          "IssueType" => "controlStrict", 
          "TicketType" => "controlStrict", 
          "MeetingType" => "controlStrict", 
          "MessageType" => "controlStrict", 
          "OpportunityType" => "controlStrict", 
          "PaymentType" => "controlStrict", 
          "ProjectExpenseType" => "controlStrict", 
          "ProjectType" => "controlStrict", 
          "QuestionType" => "controlStrict", 
          "QuotationType" => "controlStrict", 
          "RequirementType" => "controlStrict", 
          "MilestoneType" => "controlStrict", 
          "RiskType" => "controlStrict", 
          "TestCaseType" => "controlStrict", 
          "TestSessionType" => "controlStrict", 
          "VersioningType" => "controlStrict", 
          "WorkflowStatus" => "cascade"));

  private static $_closeRelationShip = array(
      "AccessScopeRead" => array(
          "AccessProfile" => "control"), 
      "AccessScopeCreate" => array(
          "AccessProfile" => "control"), 
      "AccessScopeUpdate" => array(
          "AccessProfile" => "control"), 
      "AccessScopeDelete" => array(
          "AccessProfile" => "control"), 
      "Activity" => array(
          "Assignment" => "cascade",
          "Activity" => "confirm",
          "Meeting" => "confirm",
          "Milestone" => "confirm",
          "Note" => "cascade",
          "PeriodicMeeting" => "confirm",
          "TestSession"=>"confirm",
          "Ticket" => "control",
          "Link" => "cascade",          
      ), 
      "Contact" => array(
          "Affectation" => "cascade"),
      "Document" => array(
          "DocumentVersion" => "cascade"), 
      "DocumentDirectory" => array(
          "Document" => "control", 
          "DocumentDirectory" => "control"), 
// MTY - LEAVE SYSTEM
      "EmploymentContractEndReason" => array(
          "EmploymentContract" => "control"
      ),
      "EmploymentContractType" => array(
          "EmploymentContract" => "control",
          "CustomEarnedRulesOfEmploymentContractType" => "cascade",
          "LeaveTypeOfEmploymentContractType" => "cascade"
      ),
      "LeaveType" => array(
          "CustomEarnedRulesOfEmploymentContractType" => "control",
          "EmployeeLeaveEarned" => "control",
          "Leave" => "control",
          "LeaveTypeOfEmploymentContractType" => "control"          
      ),
// MTY - LEAVE SYSTEM
      "Meeting" => array(
          "Assignment" => "cascade"
      ),
// BEGIN - ADD BY TABARY - NOTIFICATION SYSTEM      
      "Notifiable" => array(
          "NotificationDefinition" => "confirm"),
// END - ADD BY TABARY - NOTIFICATION SYSTEM      
      "PeriodicMeeting" => array(
          "Meeting" => "control"), 
      "Product" => array(
          "Version" => "control", 
          "Requirement" => "confirm", 
          "TestCase" => "confirm", 
          "TestSession" => "control"), 
      "Project" => array(
          "Action" => "confirm", 
          "Activity" => "control", 
          "Affectation" => "cascade", 
          "CallForTender" => "control", 
          "Command" => "control", 
          "Document" => "confirm", 
          "Issue" => "confirm", 
      		"IndicatorDefinitionPerProject" => "cascade",
          "IndividualExpense" => "confirm", 
          "ProjectExpense" => "confirm", 
          "Term" => "confirm", 
          "Bill" => "confirm", 
          "Milestone" => "confirm", 
          "Project" => "control", 
          "Risk" => "confirm", 
          "Ticket" => "control", 
          "Decision" => "confirm", 
          "Meeting" => "confirm", 
          "Opportunity" => "confirm", 
          "PeriodicMeeting" => "confirm", 
          "ProviderBill"=> "control",
          "ProviderOrder"=> "control",
          "VersionProject" => "cascade", 
          "Question" => "confirm", 
          "Quotation" => "confirm", 
          "Requirement" => "confirm", 
      		"StatusMailPerProject" => "cascade",
          "Tender" => "control", 
          "TestCase" => "confirm", 
          "TestSession" => "confirm", 
  		    "TicketDelayPerProject" => "cascade"),
      "Requirement" => array("Requirement" => "control"), 
      "Resource" => array(
          "Action" => "control", 
          "Activity" => "control", 
          "Affectation" => "cascade", 
          "Assignment" => "cascade", 
          "Issue" => "control", 
          "Milestone" => "control", 
          "Risk" => "control", 
          "Ticket" => "control", 
          "Decision" => "control", 
          "Meeting" => "control", 
          "Question" => "control", 
          "Requirement" => "control", 
          "ResourceTeamAffectation" => "cascade",
          "TestCase" => "control", 
          "TestSession" => "control"), 
      "ResourceTeam" => array(
          "ResourceTeamAffectation" => "cascade"),      
      "TestCase" => array(
          "TestCase" => "confirm", 
          "TestCaseRun" => "cascade"), 
      "TestSession" => array(
          "TestCaseRun" => "cascade",
          "Assignment" => "cascade"),
      "User" => array(
          "Affectation" => "cascade"), 
      "Version" => array(
          "VersionProject" => "cascade", 
          "TestSession" => "confirm"));

  //ELIOTT - LEAVE SYSTEM
  private static $_classesArrayToBypassPHPCompatibilityIf=array(
      0=>"EmployeeLeaveEarned",
      1=>"LeaveTypeOfEmploymentContractType",
      2=>"CustomEarnedRulesOfEmploymentContractType"
  );
  
  private static $_attributesArrayToBypassPHPCompatibilityIf=array(
      0=>"quantity",
      1=>"leftQuantity",
      2=>"oldLeftQuantity",
      3=>"leftQuantityBeforeClose"
  );
  //ELIOTT - LEAVE SYSTEM
  /**
   * =========================================================================
   * Constructor.
   * Protected because this class must be extended.
   *
   * @param $id the
   *          id of the object in the database (null if not stored yet)
   * @return void
   */
  protected function __construct($id = NULL, $withoutDependentObjects = false) {
    if (trim ( $id ) and ! is_numeric ( $id )) {
      $class = get_class ( $this );
      traceHack ( "SqlElement->_construct : id '$id' is not numeric for class $class" );
      return;
    }
    $this->id = $id;
    if ($this->id == '') {
      $this->id = null;
    }
    $this->getSqlElement ( $withoutDependentObjects );
  }

  /**
   * =========================================================================
   * Destructor
   *
   * @return void
   */
  protected function __destruct() {}
  
  // ============================================================================**********
  // UPDATE FUNCTIONS
  // ============================================================================**********
  
  /**
   * =========================================================================
   * Give public visibility to the saveSqlElement action
   *
   * @param
   *          force to avoid controls and force saving even if controls are false
   * @return message including definition of html hiddenfields to be used
   */
  public function save() {
    global $debugTraceUpdates, $debugTraceHistory;
    if ( isset($debugTraceUpdates) and $debugTraceUpdates==true) {
      if ( ! property_exists($this,'_noHistory') or (isset($debugTraceHistory) and $debugTraceHistory==true) )
        debugTraceLog("Start SAVE for ".get_class($this)." #".$this->id.((SqlElement::is_a($this, 'PlanningElement'))?" $this->refType #$this->refId":''));$startMicroTime=microtime(true);
    }
    $peName=get_class($this).'PlanningElement';
    if (Parameter::getGlobalParameter ( 'autoUpdateActivityStatus' ) == 'YES' and property_exists($this,$peName) and !isset($old)) {
      $old=$this->getOld();
    }
    if (property_exists($this,'idMilestone') and !isset($old)) {
      $old=$this->getOld();
    }
    if (property_exists($this,'tags') and !isset($old)) {
      $old=$this->getOld();
    }
    if (isset ( $this->_onlyCallSpecificSaveFunction ) and $this->_onlyCallSpecificSaveFunction == true)
      return;
    $this->setAllDefaultValues ( true );
    if (! property_exists ( $this, '_onlyCallSpecificSaveFunction' ) or ! $this->_onlyCallSpecificSaveFunction) {
      // PlugIn Management
      $lstPluginEvt = Plugin::getEventScripts ( 'beforeSave', get_class ( $this ) );
      foreach ( $lstPluginEvt as $script ) {
        require $script; // execute code
      }
    }
    if(property_exists($this,'idProduct') and property_exists($this,'idTargetProductVersion')){
      if($this->idTargetProductVersion and !$this->idProduct){
        $getVersion = new Version($this->idTargetProductVersion);
        $this->idProduct = $getVersion->idProduct;
      }
    }
    if (property_exists($this,'idMilestone') and Parameter::getGlobalParameter('milestoneFromVersion')=='YES' 
    and (   ( property_exists($this,'idTargetProductVersion') and $this->idTargetProductVersion) 
         or ( property_exists($this,'idProductVersion') and $this->idProductVersion) 
        ) ) {
      $pv=new ProductVersion(((property_exists($this,'idTargetProductVersion'))?$this->idTargetProductVersion:$this->idProductVersion),true);
      if ($pv->idMilestone) {
        $this->idMilestone=$pv->idMilestone;
      }
    }
    $result = $this->saveSqlElement ();
    if (getLastOperationStatus($result=='OK') and property_exists($this, 'tags')) {
      // Save tag list is object tag
      Tag::saveTagList($this->tags,$old->tags,get_class($this));
      
    }
    if (! property_exists ( $this, '_onlyCallSpecificSaveFunction' ) or ! $this->_onlyCallSpecificSaveFunction) {
      // PlugIn Management
      $lstPluginEvt = Plugin::getEventScripts ( 'afterSave', get_class ( $this ) );
      foreach ( $lstPluginEvt as $script ) {
        require $script; // execute code
      }
    }
    if (property_exists($this,'idMilestone') and $this->idMilestone and $old->idMilestone!=$this->idMilestone ) {
      $pe=SqlElement::getSingleSqlElementFromCriteria('MilestonePlanningElement', array('refType'=>'Milestone','refId'=>$this->idMilestone));
      if ($pe and $pe->id) {
        $pe->updateMilestonableItems(get_class($this),$this->id);
      }
    }
    // ticket #2822 - mehdi
    //$arrayStatusable("Project","Activity","Milestone","Meeting","TestSession");
    //$peName=get_class($this).'PlanningElement';
    if (Parameter::getGlobalParameter ( 'autoUpdateActivityStatus' ) == 'YES' and property_exists($this,$peName) ) {
      $pe=$this->$peName;
      if (!$old->id or !$pe->topId or ! property_exists($this,'idStatus') or ! property_exists($this,'handled') or ! property_exists($this,'handledDate')) return $result;
      //$parentPe=new PlanningElement($pe->topId);
      $parentType=$pe->topRefType;
      $parent=new $parentType($pe->topRefId);
      if (! property_exists($parent,'idStatus') or ! property_exists($parent,'handled') or ! property_exists($parent,'handledDate')) return $result;
      if ($this->handled and $this->handled!=$old->handled) {
        if ( ! $parent->handled ) {
          $parent->handled = $this->handled;
          $parent->handledDate=date('Y-m-d');
          $allowedStatusList=Workflow::getAllowedStatusListForObject($parent);
          foreach ( $allowedStatusList as $st ) {
            if ($st->setHandledStatus) {
              $parent->idStatus=$st->id;
              $parent->save();
              break;
            }
          }
        }
      }
      $status = new Status ($this->idStatus);
      $isStatDone=($status->setDoneStatus)?true:false;
      $isStatIdle=($status->setIdleStatus)?true:false;
      $isStatCancelled=($status->setCancelledStatus)?true:false;
      $status = new Status ($old->idStatus);
      $isOldStatDone=($status->setDoneStatus)?true:false;
      $isOldStatIdle=($status->setIdleStatus)?true:false;
      $isOldStatCancelled=($status->setCancelledStatus)?true:false;
      SqlElement::$_cachedQuery ['Status']=array();
      $st=new Status();
      $statusList=$st->getSqlElementsFromCriteria(null,null,null,null,true);
      if ( ($isStatDone and $isStatDone!=$isOldStatDone) or ($isStatIdle and $isStatIdle!=$isOldStatIdle) or ($isStatCancelled and $isStatCancelled!=$isOldStatCancelled)) {
        $allDone=true;
        $allIdle=true;
        $allCancelled=true;
        $peobj=new PlanningElement();
        $sons=$peobj->getSqlElementsFromCriteria(null, null, "topId=$pe->topId");
        foreach ($sons as $sonPe) {
          $sonType=$sonPe->refType;
          $son=new $sonType($sonPe->refId);       
          // Change : only base on Status...
          if (! property_exists($son,'idStatus') or !$son->idStatus) continue;
          if (!isset($statusList['#'.$son->idStatus])) continue;
          $sonStatus=$statusList['#'.$son->idStatus];
          if (!$sonStatus->setDoneStatus and !$sonStatus->setCancelledStatus) $allDone=false;
          if (!$sonStatus->setIdleStatus and !$sonStatus->setCancelledStatus) $allIdle=false;
          if (!$sonStatus->setCancelledStatus) $allCancelled=false;
          //if (! property_exists($son,'done') or ! property_exists($son,'idle') or ! property_exists($son,'cancelled')) continue;
          //if (!$son->done and !$son->cancelled) $allDone=false;
          //if (!$son->idle and !$son->cancelled) $allIdle=false;
          //if (!$son->cancelled) $allCancelled=false;
        }
        $setToDone=($isStatDone and $isStatDone!=$isOldStatDone and $allDone)?true:false;
        $setToIdle=($isStatIdle and $isStatIdle!=$isOldStatIdle and $allIdle)?true:false;
        $setToCancelled=($isStatCancelled and $isStatCancelled!=$isOldStatCancelled and $allCancelled)?true:false;
        if ($setToDone or $setToIdle or $setToCancelled) {
          $currentParentStatus=new Status($parent->idStatus);
          if ( (! $setToDone or ($setToDone and $currentParentStatus->setDoneStatus) )
              and (! $setToIdle or ($setToIdle and $currentParentStatus->setIdleStatus) )
              and (! $setToCancelled or ($setToCancelled and $currentParentStatus->setCancelledStatus) )) {
                // Nothing to do, already in a status corresponding to target
              } else {
                $allowedStatusList=Workflow::getAllowedStatusListForObject($parent);
                $saveParent=false;
                foreach ( $allowedStatusList as $st ) {
                  if ($setToDone and $st->setDoneStatus and property_exists($parent,'done') and property_exists($parent,'doneDate')) {
                    $parent->idStatus=$st->id;
                    $parent->done=$this->done;
                    $parent->doneDate=date('Y-m-d');
                    $saveParent=true;
                    $setToDone=false;
                  }
                  if ($setToIdle and $st->setIdleStatus and property_exists($parent,'idle') and property_exists($parent,'idleDate')) {
                    $parent->idStatus=$st->id;
                    $parent->idle=$this->idle;
                    $parent->idleDate=date('Y-m-d');
                    $saveParent=true;
                    $setToIdle=false;
                  }
                  if ($setToCancelled and $st->setCancelledStatus and property_exists($parent,'cancelled') ) {
                    $parent->idStatus=$st->id;
                    $parent->cancelled=$this->cancelled;
                    //$parent->doneDate=date('Y-m-d');
                    $saveParent=true;
                    $setToCancelled=false;
                  }
                }
                if ($saveParent) {
                  $resParent=$parent->save();
                }
              }
        }
      }
    }
    if (isset($debugTraceUpdates) and $debugTraceUpdates==true) {
      if ( ! property_exists($this,'_noHistory') or (isset($debugTraceHistory) and $debugTraceHistory==true) )
        debugTraceLog("  End SAVE for ".get_class($this)." #".$this->id.((SqlElement::is_a($this, 'PlanningElement'))?" $this->refType #$this->refId":'')." => ".round((microtime(true) - $startMicroTime)*1000000)/1000000);
    }
    return $result;
  }

  public function insert() { // Specific function to force insert with a defined id - Reserved to Import fonction
    $this->_onlyCallSpecificSaveFunction = true;
    // PlugIn Management
    $lstPluginEvt = Plugin::getEventScripts ( 'beforeSave', get_class ( $this ) );
    foreach ( $lstPluginEvt as $script ) {
      require $script; // execute code
    }
    $this->save (); // To force the update of fields calculated in the save function ...
    $this->_onlyCallSpecificSaveFunction = false;
    $result = $this->saveSqlElement ( false, false, true );
    // PlugIn Management
    $lstPluginEvt = Plugin::getEventScripts ( 'afterSave', get_class ( $this ) );
    foreach ( $lstPluginEvt as $script ) {
      require $script; // execute code
    }
    return $result;
  }

  public function saveForced($withoutDependencies = false) {
    // PlugIn Management
    $lstPluginEvt = Plugin::getEventScripts ( 'beforeSave', get_class ( $this ) );
    foreach ( $lstPluginEvt as $script ) {
      require $script; // execute code
    }
    $result = $this->saveSqlElement ( true, $withoutDependencies );
    // PlugIn Management
    $lstPluginEvt = Plugin::getEventScripts ( 'afterSave', get_class ( $this ) );
    foreach ( $lstPluginEvt as $script ) {
      require $script; // execute code
    }
    return $result;
  }
  
  // Save without controls and without extra save() feature defined in local save() method (for corresponding class)
  public function simpleSave() {
    return $this->saveForced();
  }

  /**
   * =========================================================================
   * Give public visibility to the purgeSqlElement action
   *
   * @return message including definition of html hiddenfields to be used
   */
  public function purge($clause) {
    return $this->purgeSqlElement ( $clause );
  }

  /**
   * =========================================================================
   * Give public visibility to the closeSqlElement action
   *
   * @return message including definition of html hiddenfields to be used
   */
  public function close($clause) {
    return $this->closeSqlElement ( $clause );
  }

  /**
   * =========================================================================
   * Give public visibility to the deleteSqlElement action
   *
   * @return message including definition of html hiddenfields to be used
   */
  public function delete() {
    global $debugTraceUpdates, $debugTraceHistory;
    // PlugIn Management
    if (isset($debugTraceUpdates) and $debugTraceUpdates==true) {
      if ( ! property_exists($this,'_noHistory') or (isset($debugTraceHistory) and $debugTraceHistory==true) )
        debugTraceLog("Start DELETE for ".get_class($this)." #".$this->id);$startMicroTime=microtime(true);
    }
    $list = Plugin::getEventScripts ( 'beforeDelete', get_class ( $this ) );
    foreach ( $list as $script ) {
      require $script; // execute code
    }
    $result = $this->deleteSqlElement ();
    // PlugIn Management
    $list = Plugin::getEventScripts ( 'afterDelete', get_class ( $this ) );
    foreach ( $list as $script ) {
      require $script; // execute code
    }
    if (isset($debugTraceUpdates) and $debugTraceUpdates==true) {
      if ( ! property_exists($this,'_noHistory') or (isset($debugTraceHistory) and $debugTraceHistory==true) )
        debugTraceLog("End DELETE for ".get_class($this)." #".$this->id." => ".round((microtime(true) - $startMicroTime)*1000000)/1000000);
    }
    return $result;
  }

  /**
   * =========================================================================
   * Give public visibility to the copySqlElement action
   *
   * @return the new object
   */
  public function copy() {
    global $debugTraceUpdates,$debugTraceHistory;
    if (isset($debugTraceUpdates) and $debugTraceUpdates==true) {
      if ( ! property_exists($this,'_noHistory') or (isset($debugTraceHistory) and $debugTraceHistory==true) )
        debugTraceLog("Start COPY for ".get_class($this)." #".$this->id);$startMicroTime=microtime(true);
    }
    self::setCopyInProgress();
    $result= $this->copySqlElement ();
    if (isset($debugTraceUpdates) and $debugTraceUpdates==true) {
      if ( ! property_exists($this,'_noHistory') or (isset($debugTraceHistory) and $debugTraceHistory==true) )
        debugTraceLog("End COPY for ".get_class($this)." #".$this->id." => ".round((microtime(true) - $startMicroTime)*1000000)/1000000);
    }
    return $result;
  }

  public function copyTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments = false, $withAffectations = false, $toProject = null, $toActivity = null, $copyToWithResult = false, $copyToWithVersionProjects = false) {
    global $debugTraceUpdates,$debugTraceHistory;
    if (isset($debugTraceUpdates) and $debugTraceUpdates==true) {
      if ( ! property_exists($this,'_noHistory') or (isset($debugTraceHistory) and $debugTraceHistory==true) )
        debugTraceLog("Start COPYTO for ".get_class($this)." #".$this->id);$startMicroTime=microtime(true);
    }
    self::setCopyInProgress();
    $result=$this->copySqlElementTo ( $newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments, $withAffectations, $toProject, $toActivity, $copyToWithResult );
    if (isset($debugTraceUpdates) and $debugTraceUpdates==true) {
      if ( ! property_exists($this,'_noHistory') or (isset($debugTraceHistory) and $debugTraceHistory==true) )
        debugTraceLog("End COPYTO for ".get_class($this)." #".$this->id." => ".round((microtime(true) - $startMicroTime)*1000000)/1000000);
    }
    return $result;
  }
  
  public static function setCopyInProgress() {
    self::$_copyInProgress=true;
  }
  
  public static function isCopyInProgress() {
    if (isset(self::$_copyInProgress) and self::$_copyInProgress==true) {
      return true;
    } else {
      return false;
    }
  }
  
  

  /**
   * =========================================================================
   * Save an object to the database
   *
   * @return void
   */
  private function saveSqlElement($force = false, $withoutDependencies = false, $forceInsert = false) {
    // traceLog("saveSqlElement(" . get_class($this) . "#$this->id)"
    // . ((SqlElement::is_subclass_of($this,'PlanningElement'))?" => $this->refType #$this->refId":''));
    // if (get_class($this)=='History') traceLog(" => $this->colName : '$this->oldValue'->'$this->newValue'");
    // #305
    $this->recalculateCheckboxes ();
    // select operation to be executed
    if ($force) {
      $control = "OK";
    } else {
      $control = $this->control();
      $class = get_class($this);
      if (($control == 'OK' or strpos ( $control, 'id="confirmControl" value="save"' ) > 0) and property_exists ( $class, $class . 'PlanningElement' )) {
        $pe = $class . 'PlanningElement';
        $controlPe = $this->$pe->control();
        if ($controlPe != 'OK') {
          $control = $controlPe;
        }
      }
      if (($control == 'OK' or strpos ( $control, 'id="confirmControl" value="save"' ) > 0) and property_exists ( $this, 'WorkElement' ) and isset ( $this->WorkElement )) {
        $we = 'WorkElement';
        $controlWe = $this->WorkElement->control();
        if ($controlWe != 'OK') {
          $control = $controlWe;
        }
      }
      if (($control == 'OK' or strpos ( $control, 'id="confirmControl" value="save"' ) > 0) and property_exists ( $class, 'OrganizationBudgetElementCurrent' ) and 
      // ADD BY TABARY Marc - 2017-06-06 - USE OR NOT ORGANIZATION BUDGETELEMENT
      Parameter::getGlobalParameter ( 'useOrganizationBudgetElement' ) === "YES") {
        $be = 'OrganizationBudgetElementCurrent';
        $controlBe = $this->$be->control ();
        if ($controlBe != 'OK') {
          $control = $controlBe;
        }
      }
    }
    if ($control == "OK") {
      // $old=new Project();
      if (property_exists ( $this, 'idStatus' ) or property_exists ( $this, 'reference' ) or property_exists ( $this, 'idResource' ) or property_exists ( $this, 'description' ) or property_exists ( $this, 'result' )) {
        $class = get_class ( $this );
        $old = new $class ( $this->id );
      }
      $statusChanged = false;
      $responsibleChanged = false;
      $descriptionChange = false;
      $resultChange = false;
      if (property_exists ( $this, 'reference' ) and isset ( $old )) {
        $this->setReference ( false, $old );
      }
      if (property_exists ( $this, 'idResource' ) and ! trim ( $this->idResource ) and !SqlElement::isCopyInProgress()) {
        $this->setDefaultResponsible ();
      }
      if (! $this->id and $this instanceof PlanningElement) { // For planning element , check that not existing yet
        $critPe = array('refType' => $this->refType, 'refId' => $this->refId);
        $pe = SqlElement::getSingleSqlElementFromCriteria ( 'PlanningElement', $critPe );
        if ($pe->id) {
          $this->id = $pe->id;
        }
      }
      if ($this->id != null and ! $forceInsert) {
        if (property_exists ( $this, 'idStatus' )) {
          if ($this->idStatus and isset ( $old )) {
            if ($old->idStatus != $this->idStatus) {
              $statusChanged = true;
            }
          }
        }
        $newItem = false;
        $returnValue = $this->updateSqlElement ( $force, $withoutDependencies );
      } else {
      	if (property_exists ( $this, 'idStatus' )) {
        	$statusChanged = true;
      	}
        $newItem = true;
        $returnValue = $this->insertSqlElement ( $forceInsert );
      }
      if (property_exists ( $this, 'idResource' ) and ! $newItem and isset ( $old )) {
        if (trim ( $this->idResource ) and trim ( $this->idResource ) != trim ( $old->idResource )) {
          $responsibleChanged = true;
        }
      }
      if (property_exists ( $this, 'description' ) and ! $newItem and isset ( $old )) {
        if (trim($this->description) != trim($old->description))  {
          $descriptionChange = true;
        }
      }
      if (property_exists ( $this, 'result' ) and ! $newItem and isset ( $old )) {
        if ($this->result != $old->result) {
          $resultChange = true;
        }
      }
      // if (($statusChanged or $responsibleChanged) and stripos($returnValue,'id="lastOperationStatus" value="OK"')>0 ) {
      
      if (stripos ( $returnValue, 'id="lastOperationStatus" value="OK"' ) > 0 and ! property_exists($this,'_noHistory')
      and ! SqlElement::is_a($this, 'PlanningElement')) {
        $mailResult = $this->sendMailIfMailable ( $newItem, $statusChanged, false, $responsibleChanged, false, false, false, $descriptionChange, $resultChange, false, false, true,false,false );
        if ($mailResult) {
          $returnValue = str_replace ( '${mailMsg}', ' - ' . Mail::getResultMessage($mailResult), $returnValue );
        } else {
          $returnValue = str_replace ( '${mailMsg}', '', $returnValue );
        }
      } else {
        $returnValue = str_replace ( '${mailMsg}', '', $returnValue );
      }
      // indicators
      $classIndicatorable=(SqlElement::is_a($this, 'PlanningElement'))?$this->refType:get_class($this);
      global $doNotTriggerAlerts;
      if (SqlList::getIdFromTranslatableName ( 'Indicatorable', $classIndicatorable ) and ! $doNotTriggerAlerts) {
        $indDef = new IndicatorDefinition ();
        if(property_exists($this, 'idProject')) {
          $idP=(get_class($this)=='Project')?$this->id:$this->idProject;
          $proj= new Project($idP); 
          $listProj = $proj->getTopProjectList(true);
          $listProj = implode(',', $listProj);
          if (trim($listProj)=='') $listProj='0';
          //$crit = array('nameIndicatorable' => get_class ( $this ), 'idle' => '0','idProject' => $idP);
          $where = "nameIndicatorable='$classIndicatorable' and idle = 0 and idProject in (".$listProj.")";
        } else {
          //$crit = array('nameIndicatorable' => get_class ( $this ), 'idle' => '0');
          $where = "nameIndicatorable='$classIndicatorable' and idle = 0";
        }
        $lstInd = $indDef->getSqlElementsFromCriteria ( null, false, $where );
        if(!$lstInd){
          $crit = array('nameIndicatorable' => $classIndicatorable, 'idle' => '0','idProject' =>"");
          $lstInd = $indDef->getSqlElementsFromCriteria ( $crit, false );
        }
        foreach ( $lstInd as $ind ) {
          $fldType = 'id' . (($classIndicatorable == 'TicketSimple') ? 'Ticket' : $classIndicatorable) . 'Type';
          if (SqlElement::is_a($this, 'PlanningElement')) {
            $objType=new $classIndicatorable($this->refId);
            $fldVal=$objType->$fldType;
          } else {
            $objType=$this;
            $fldVal=$this->$fldType;
          }
          if (! $ind->idType or $ind->idType == $fldVal) {
            IndicatorValue::addIndicatorValue ( $ind, $objType );
          }
        }
      }
      if (property_exists ( $this, 'idle' ) and $this->idle) {
        $this->dispatchClose ();
      }
      return $returnValue;
    } else {
      // errors on control => don't save, display error message
      if (strpos ( $control, 'id="confirmControl" value="save"' ) > 0) {
        $returnValue = '<b>' . i18n ( 'messageConfirmationNeeded' ) . '</b><br/>' . $control;
        $returnValue .= '<input type="hidden" id="lastOperationStatus" value="CONFIRM" />';
      } else {
        $returnValue = '<b>' . i18n ( 'messageInvalidControls' ) . '</b><br/>' . $control;
        $returnValue .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
      }
      $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
      $returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
      return $returnValue;
    }
  }

  private function dispatchClose() {
    global $mode;
    if (property_exists ( $this, 'idle' ) and $this->idle) {
      $relationShip = self::$_closeRelationShip;
      $user = getSessionUser ();
      $crit = array('idProfile' => $user->getProfile ( $this ), 'scope' => 'canForceClose');
      $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', $crit );
      $canForceClose=false;
      if ($habil and $habil->id and $habil->rightAccess == '1') {
        $canForceClose = true;
      }
      if (array_key_exists ( get_class ( $this ), $relationShip )) {
        $objects = '';
        $error = false;
        foreach ( $relationShip [get_class ( $this )] as $object=>$mode ) {
          if($canForceClose and $mode=='control'){
            $mode = "confirm";
          }
          if (($mode == 'cascade' or $mode == 'confirm') and property_exists ( $object, 'idle' )) {
            $where = null;
            $obj = new $object ();
            $crit = array('id' . get_class ( $this ) => $this->id, 'idle' => '0');
            if (property_exists ( $obj, 'refType' ) and property_exists ( $obj, 'refId' )) {
              if (property_exists ( $obj, 'id' . get_class ( $this ) )) {
                $crit = null;
                $where = "(id" . get_class ( $this ) . "=" . $this->id . " or (refType='" . get_class ( $this ) . "' and refId=" . $this->id . ")) and idle=0";
              } else {
                $crit = array("refType" => get_class ( $this ), "refId" => $this->id, "idle" => '0');
              }
            }
            if ($object == "Dependency") {
              $crit = null;
              $where = "idle=0 and ((predecessorRefType='" . get_class ( $this ) . "' and predecessorRefId=" . $this->id . ")" . " or (successorRefType='" . get_class ( $this ) . "' and successorRefId=" . $this->id . "))";
            }
            if ($object == "Link") {
              $crit = null;
              $where = "idle=0 and ((ref1Type='" . get_class ( $this ) . "' and ref1Id=" . Sql::fmtId ( $this->id ) . ")" . " or (ref2Type='" . get_class ( $this ) . "' and ref2Id=" . Sql::fmtId ( $this->id ) . "))";
            }
            $list = $obj->getSqlElementsFromCriteria ( $crit, false, $where );
            foreach ( $list as $o ) {
              $o->idle = 1;
              if (property_exists ( $o, 'idleDate' ) and ! trim ( $o->idleDate )) {
                $o->idleDate = date ( 'Y-m-d' );
              }
              if (property_exists ( $o, 'idleDateTime' ) and ! trim ( $o->idleDateTime )) {
                $o->idleDateTime = date ( 'Y-m-d H:i:s' );
              }
              $resO = $o->save ();
            }
          }
        }
        if ($objects != "") {
          if ($error) {
            $result .= "<br/>" . i18n ( "errorControlClose" ) . $objects;
          } else {
            $result .= '<input type="hidden" id="confirmControl" value="save" /><br/>' . i18n ( "confirmControlSave" ) . $objects;
          }
        }
      }
    }
  }

  /**
   * =========================================================================
   * Save an object to the database : new object
   *
   * @return void
   */
  private function insertSqlElement($forceInsert = false) {
    if (get_class ( $this ) == 'Origin') {
      if (! $this->originId or ! $this->originType) {
        return;
      }
    }
    $depedantObjects = array();
    $returnStatus = "OK";
    $objectClass = get_class ( $this );
    $query = "insert into " . $this->getDatabaseTableName ();
    $queryColumns = "";
    $queryValues = "";
    // initialize object definition criteria
    $databaseCriteriaList = $this->getDatabaseCriteria ();
    foreach ( $databaseCriteriaList as $col_name => $col_value ) {
      $dataType = $this->getDataType ( $col_name );
      $dataLength = $this->getDataLength ( $col_name );
      $attribute = $this->getFieldAttributes ( $col_name );
      if (strpos ( $attribute, 'calculated' ) === false) {
        if ($dataType == 'int' and $dataLength == 1) {
          if ($col_value == NULL or $col_value == "") {
            $col_value = '0';
          }
        }
        if ($col_value != NULL and $col_value != '' and $col_value != ' ' and ($col_name != 'id' or $forceInsert)) {
          if ($queryColumns != "") {
            $queryColumns .= ", ";
            $queryValues .= ", ";
          }
          $queryColumns .= $this->getDatabaseColumnName ( $col_name );
          $queryValues .= Sql::str ( $col_value, $objectClass );
        }
      }
    }
    if (Sql::isPgsql ()) {
      $queryColumns = strtolower ( $queryColumns );
    }
    if (property_exists ( $this, 'lastUpdateDateTime' ) and !SqlElement::$_doNotSaveLastUpdateDateTime) { // Initialize lastUpdateDateTime (for tickets)
      $this->lastUpdateDateTime = date ( 'Y-m-d H:i:s' );
    }
    // get all data
    foreach ( $this as $col_name => $col_value ) {
      $attribute = $this->getFieldAttributes ( $col_name );
      if (strpos ( $attribute, 'calculated' ) === false) {
        if (substr ( $col_name, 0, 1 ) == "_") {
          // not a fiels, just for presentation purpose
        } else if (ucfirst ( $col_name ) == $col_name) {
          // if property is an object, store it to save it at the end of script
          $depedantObjects [$col_name] = ($this->$col_name);
        } else if (array_key_exists ( $col_name, $databaseCriteriaList )) {
          // Do not overwrite the default value from databaseCriteria, and do not double-set in insert clause
        } else {
          $dataType = $this->getDataType ( $col_name );
          $dataLength = $this->getDataLength ( $col_name );
          if ($dataType == 'int' and $dataLength == 1) {
            if ($col_value == NULL or $col_value == "") {
              $col_value = '0';
            }
          }
          if ($dataLength > 4000 and getEditorType () == 'text') {
            $col_value = nl2brForPlainText ( $col_value );
          }
          if ($col_value != NULL and $col_value != '' and $col_value != ' ' and ($col_name != 'id' or $forceInsert) and strpos ( $queryColumns, ' ' . $this->getDatabaseColumnName ( $col_name ) . ' ' ) === false) {
            if ($queryColumns != "") {
              $queryColumns .= ",";
              $queryValues .= ", ";
            }
            $queryColumns .= ' ' . $this->getDatabaseColumnName ( $col_name ) . ' ';
            $queryValues .= Sql::str ( $col_value, $objectClass );
          }
        }
      }
    }
    $query .= " ($queryColumns) values ($queryValues)";
    // execute request
    $result = Sql::query ( $query );
    if (! $result) {
      $returnStatus = "ERROR";
    }
    // save history
    $newId = Sql::$lastQueryNewid;
    $this->id = $newId;
    if ($returnStatus != "ERROR" and ! property_exists ( $this, '_noHistory' )) {
      $result = History::store ( $this, $objectClass, $newId, 'insert' );
      if (! $result) {
        $returnStatus = "ERROR";
      }
    }
    // save depedant elements (properties that are objects)
    if ($returnStatus != "ERROR" and $returnStatus != "INVALID") {
      $returnStatus = $this->saveDependantObjects ( $depedantObjects, $returnStatus );
      if ($returnStatus == "ERROR") {
        $returnValue = Sql::$lastQueryErrorMessage;
      } else if ($returnStatus == "OK") {
        $returnValue = i18n ( get_class ( $this ) ) . ' #' . htmlEncode ( $this->id ) . ' ' . i18n ( 'resultUpdated' );
      } else if ($returnStatus == "NO_CHANGE" or $returnStatus == "INCOMPLETE" or $returnStatus == "WARNING" or $returnStatus == "CONFIRM") {
        // OK
      } else if (getLastOperationStatus ( $returnStatus ) == 'INVALID') {
        return $returnStatus;
      }
    }
    // Prepare return data
    if ($returnStatus != "ERROR") {
      $returnValue = i18n ( get_class ( $this ) ) . ' #' . htmlEncode ( $this->id ) . ' ' . i18n ( 'resultInserted' );
    } else {
      $returnValue = Sql::$lastQueryErrorMessage;
    }
    if ($returnStatus == "OK") {
      $returnValue .= '${mailMsg}';
    }
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="insert" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
    return $returnValue;
  }

  /**
   * Get old values (stored in session) to :
   * 1) build the smallest query
   * 2) save change history
   *
   * @param string $objectClass          
   * @param string $force          
   * @return Ambigous <NULL, unknown>
   */
  public static function getCurrentObject($objectClass = null, $objectId = null, $throwError = false, $force = false, $isComboDetail = false) {
    $oldObject = null;
    if ($force) {
      if ($objectClass) {
        return new $objectClass ( $objectId );
      } else {
        return null;
      }
    }
    if (isset ( $_REQUEST ['directAccessIndex'] )) {
      if (sessionTableValueExist ( 'directAccessIndex', $_REQUEST ['directAccessIndex'] . (($isComboDetail) ? '_comboDetail' : '') )) {
        $testObject = getSessionTableValue ( 'directAccessIndex', $_REQUEST ['directAccessIndex'] . (($isComboDetail) ? '_comboDetail' : '') );
        if (! $objectClass or (get_class ( $testObject ) == $objectClass and $testObject->id==$objectId) ) {
          $oldObject = $testObject;
        } else if ($throwError) {
          throwError ( 'currentObject (' . get_class ( $testObject ) . ' #' . $obj->id . ') is not of the expectec class (' . $objectClass . ')' );
          return null;
        }
      } else if ($throwError) {
        throwError ( 'currentObject parameter not found in SESSION' );
        return null;
      }
    } else if (sessionValueExists ( 'currentObject' . (($isComboDetail) ? '_comboDetail' : '') )) {
      $testObject = getSessionValue ( 'currentObject' . (($isComboDetail) ? '_comboDetail' : '') );
      if (! $objectClass or (get_class ( $testObject ) == $objectClass and $testObject->id==$objectId) ) {
        $oldObject = $testObject;
      } else if ($throwError) {
        throwError ( 'currentObject (' . get_class ( $testObject ) . ' #' . $obj->id . ') is not of the expectec class (' . $objectClass . ')' );
        return null;
      }
    }
    if (! $oldObject and $objectClass) {
      $oldObject = new $objectClass ( $objectId );
    }
    return $oldObject;
  }

  public static function setCurrentObject($obj, $isComboDetail = false) {
    $obj=self::setCurrentObjectTimestamp($obj);
    if (isset ( $_REQUEST ['directAccessIndex'] )) {
      if (! sessionValueExists ( 'directAccessIndex' )) {
        setSessionValue ( 'directAccessIndex', array() );
      }
      if ($isComboDetail) {
        setSessionTableValue ( 'directAccessIndex', $_REQUEST ['directAccessIndex'] . '_comboDetail', $obj );
      } else {
        setSessionTableValue ( 'directAccessIndex', $_REQUEST ['directAccessIndex'], $obj );
      }
    } else {
      if ($isComboDetail) {
        setSessionValue ( 'currentObject_comboDetail', $obj );
      } else {
        setSessionValue ( 'currentObject', $obj );
      }
    }
  }
  
  public static function setCurrentObjectTimestamp($obj) {
    if (!$obj or ! is_object($obj)) return;
    $obj->_storageDateTime=date('Y-m-d H:i:s');
    return $obj;
  }
  public static function getCurrentObjectTimestamp($obj) {
    if (!$obj) $obj=self::getCurrentObject();
    if (!$obj or ! is_object($obj)) return;
    return $obj->_storageDateTime;
  }
  
  public static function resetCurrentObjectTimestamp($obj=null) {
    if (!$obj) $obj=self::getCurrentObject();
    if (!$obj or ! is_object($obj)) return;
    $obj->_storageDateTime=self::setCurrentObjectTimestamp($obj);
    self::setCurrentObject($obj);
    return $obj->_storageDateTime;
  }

  public static function unsetCurrentObject() {
    if (isset ( $_REQUEST ['directAccessIndex'] ) and sessionTableValueExist ( 'directAccessIndex', $_REQUEST ['directAccessIndex'] )) {
      unsetSessionTable ( 'directAccessIndex', $_REQUEST ['directAccessIndex'] );
    } else if (sessionValueExists ( 'currentObject' )) {
      unsetSessionValue ( 'currentObject' );
    }
  }

  /**
   * =========================================================================
   * save an object to the database : existing object
   *
   * @return void
   */
  private function updateSqlElement($force = false, $withoutDependencies = false) {
    // traceLog('updateSqlElement (for ' . get_class($this) . ' #' . $this->id . ')');
    $returnValue = i18n ( 'messageNoChange' ) . ' ' . i18n ( get_class ( $this ) ) . ' #' . $this->id;
    $returnStatus = 'NO_CHANGE';
    $depedantObjects = array();
    $objectClass = get_class ( $this );
    $arrayCols = array();
    if (Sql::isPgsql ())
      $arrayCols ['lastupdatedatetime'] = '$lastUpdateDateTime';
    else
      $arrayCols ['lastUpdateDateTime'] = '$lastUpdateDateTime';
    $idleChange = false;
    $projectChange = false;
    // Get old values (stored) to : 1) build the smallest query 2) save change history
    $oldObject = self::getCurrentObject ( get_class ( $this ), $this->id, false, $force );
    // Specific treatment for other versions
    $versionTypes = array(
        'Version', 
        'OriginalVersion', 
        'OriginalProductVersion', 
        'OriginalComponentVersion', 
        'TargetVersion', 
        'TargetProductVersion', 
        'TargetComponentVersion');
    foreach ( $versionTypes as $versType ) { // If version is cleared and item has other version, replace main with first other
      $otherFld = '_Other' . $versType;
      $versFld = 'id' . $versType;
      if (property_exists ( $this, $versFld ) and property_exists ( $this, $otherFld )) {
        usort ( $oldObject->$otherFld, "OtherVersion::sort" );
        foreach ( $oldObject->$otherFld as $otherVers ) {
          if (! trim ( $this->$versFld )) {
            $this->$versFld = $otherVers->idVersion;
          }
          if ($otherVers->idVersion == $this->$versFld) {
            $otherVers->delete ();
          }
        }
      }
    }
    if (property_exists ( $this, 'idClient' ) and property_exists ( $this, '_OtherClient' )) { // If client is cleared and item has other client, replace main with first other
      usort ( $oldObject->_OtherClient, "OtherClient::sort" );
      foreach ( $oldObject->_OtherClient as $otherClient ) {
        if (! trim ( $this->idClient )) {
          $this->idClient = $otherClient->idClient;
        }
        if ($otherClient->idClient == $this->idClient) {
          $otherClient->delete ();
        }
      }
    }
    $nbChanged = 0;
    $query = "update " . $this->getDatabaseTableName ();
    // get all data, and identify if changes
    foreach ( $this as $col_name => $col_new_value ) {
      $attribute = $this->getFieldAttributes ( $col_name );
      if (strpos ( $attribute, 'calculated' ) !== false) {
        // calculated field, not to be save
      } else if (substr ( $col_name, 0, 1 ) == "_") {
        // not a fiels, just for presentation purpose
      } else if (ucfirst ( $col_name ) == $col_name) {
        $depedantObjects [$col_name] = ($this->$col_name);
      } else {
        $dataType = $this->getDataType ( $col_name );
        $dataLength = $this->getDataLength ( $col_name );
        if ($dataType == 'int' and $dataLength == 1) {
          if ($col_new_value == NULL or $col_new_value == "") {
            $col_new_value = '0';
          }
        }
        $col_old_value = $oldObject->$col_name;
        // special null treatment (new value)
        // $col_new_value=Sql::str(trim($col_new_value));
        if (get_class ( $this ) != 'Parameter') { // Do not trim parameters
          $col_new_value = trim ( $col_new_value );
        }
        if ($dataType == 'decimal') {
          $col_new_value = str_replace ( ',', '.', $col_new_value );
        }
        if ($col_new_value == '') {
          $col_new_value = NULL;
        }
        ;
        // special null treatment (old value)
        // $col_old_value=SQL::str(trim($col_old_value));
        if ($col_old_value == '') {
          $col_old_value = NULL;
        }
        ;
        // if changed
        $isText = ($dataType == 'varchar' or substr ( $dataType, - 4 ) == 'text') ? true : false;
        if ($isText and $dataLength > 4000 and (getEditorType () == 'text' or Importable::importInProgress ())) {
          $textObj = new Html2Text ( $col_old_value );
          $oldText = $textObj->getText ();
          if (Importable::importInProgress ()) {
            // $oldText=encodeCSV($oldText);
            $oldText = str_replace ( "\n\n", "\n", $oldText ); // Remove double LF as they were removed during export
            $oldText = str_replace ( "\r", "", $oldText );
            $col_new_value = str_replace ( "\r", "", $col_new_value ); // Replace CRLF with LF
          }
          if (trim ( $oldText ) == trim ( $col_new_value )) {
            $col_new_value = $col_old_value; // Was not changed : preserve formatting
          } else {
            $col_new_value = nl2brForPlainText ( $col_new_value );
          }
        }
        // !!! do not insert query for last update date time unless some change is detected
        if ($col_new_value != $col_old_value or ($isText and ('x' . $col_new_value != 'x' . $col_old_value))) {
          if ( !$col_new_value and !$col_old_value and (substr($col_name,-4)=='Cost' or substr($col_name,-4)=='Work' or substr($col_name,-6)=='Amount') ) {
            continue; // do not save 0 to null or null to zero for Cost, Work and Amount
          }
          if (SqlElement::is_a($this, 'PlanningElement') and !$this->isManualProgress) {
            if (! isset($this->_workHistory) and ($col_name=='assignedWork' or $col_name=='leftWork'  or $col_name=='plannedWork' 
                                               or $col_name=='assignedCost' or $col_name=='leftCost'  or $col_name=='plannedCost')) {
              continue; // Do not update calculated fields if not coming from function updateSynthesisObj()                              
            }
          }
          if ($col_name == 'idle') {
            $idleChange = true;
          }
          if ($col_name == 'idProject' and get_class($this)!='Project') {
            $projectChange = true;
          }
          $insertableColName = $this->getDatabaseColumnName ( $col_name );
          if (Sql::isPgsql ()) {
            $insertableColName = strtolower ( $insertableColName );
          }
          if (! array_key_exists ( $insertableColName, $arrayCols )) {
            $arrayCols [$insertableColName] = $col_name;
            $query .= ($nbChanged == 0) ? " set " : ", ";
            if ($col_new_value == NULL or $col_new_value == '' or $col_new_value == "''") {
              $query .= $insertableColName . " = NULL";
            } else {
              $query .= $insertableColName . '=' . Sql::str ( $col_new_value ) . ' ';
            }
            $nbChanged += 1;
            // Save change history
            if ($objectClass != 'History' and ! property_exists ( $this, '_noHistory' ) and $col_name != 'id' and $col_name != 'lastUpdateDateTime') {
              $result = History::store ( $this, $objectClass, $this->id, 'update', $col_name, $col_old_value, $col_new_value );
              if (! $result) {
                $returnStatus = "ERROR";
                $returnValue = Sql::$lastQueryErrorMessage;
              }
            }
          }
        }
      }
    }
    if (($force or $nbChanged > 0) and property_exists ( $this, 'lastUpdateDateTime' )) {
      $insertableColName = $this->getDatabaseColumnName ( 'lastUpdateDateTime' );
      if (Sql::isPgsql ()) {
        $insertableColName = strtolower ( $insertableColName );
      }
      $query .= (($nbChanged == 0) ? ' SET ' : ', ') . $insertableColName . '=' . Sql::str ( date ( 'Y-m-d H:i:s' ) ) . ' ';
      $nbChanged += 1;
    }
    $query .= ' where id=' . $this->id;
    // If changed, execute the query
    if ($nbChanged > 0 and $returnStatus != "ERROR") {
      // Catch errors, and return error message
      $result = Sql::query ( $query );
      if ($result) {
        if (Sql::$lastQueryNbRows == 0) {
          $test = new $objectClass ( $this->id );
          if ($this->id != $test->id) {
            $returnValue = i18n ( 'messageItemDelete', array(i18n ( get_class ( $this ) ), $this->id) );
            $returnStatus = 'ERROR';
          } else {
            $returnValue = i18n ( 'messageNoChange' ) . ' ' . i18n ( get_class ( $this ) ) . ' #' . $this->id;
            $returnStatus = 'NO_CHANGE';
          }
        } else {
          $returnValue = i18n(get_class($this)).' #'.htmlEncode($this->id).' '.i18n('resultUpdated');
          $returnStatus = 'OK';
        }
      } else {
        $returnValue = Sql::$lastQueryErrorMessage;
        $returnStatus = "ERROR";
      }
    }
    
    // if object is Asignable, update assignments on idle change
    $archiv=new HistoryArchive();
    $hist=new History();
    if (property_exists($this, 'idle') and property_exists($this, '_Assignment')) {
      $ass = new Assignment ();
      $query = "update " . $ass->getDatabaseTableName ();
      $query .= " set idle='" . (($this->idle)?'1':'0') . "'";
      $query .= " where refType='" . get_class ( $this ) . "' ";
      $query .= " and refId=" . $this->id;
      $result = Sql::query ( $query );
      if (! $result) {
        $returnValue = Sql::$lastQueryErrorMessage;
        $returnStatus = 'ERROR';
      }
    }
    $canArchiveIdle=Parameter::getGlobalParameter('cronArchiveCloseItems');
    if ($canArchiveIdle=='YES' and $idleChange and $returnStatus != "ERROR") {
      $colList="";
      foreach ($hist as $fld=>$val) {
        if (substr($fld,0,1)=='_' or $fld=='id') continue;
        $col=$hist->getDatabaseColumnName($fld);
        if ($col) {
          $colList.="$col, ";
        }
      }
      $colList=substr($colList,0,-2);
      if($this->idle==1){ // Archive history for assignment
        $ass=new Assignment();
        $queryArchiv ="insert into " .$archiv->getDatabaseTableName()." ($colList) ";
        $queryArchiv.="  (select $colList from ".$hist->getDatabaseTableName()." hist where hist.refType='".get_class($ass)."' ";
        $queryArchiv.="    and hist.refId in (select id from ".$ass->getDatabaseTableName()." ass where ass.refType='".get_class($this)."' and ass.refId=".$this->id.")";
        $queryArchiv.="  )";
        $resultArch = Sql::query ( $queryArchiv );
        if (! $resultArch) {
          $returnValue = Sql::$lastQueryErrorMessage;
          $returnStatus = 'ERROR';
        }
        $deleteArch ="delete from ".$hist->getDatabaseTableName()." where refType='".get_class($ass)."' ";
        $deleteArch.="  and refId in (select id from ".$ass->getDatabaseTableName()." ass where ass.refType='".get_class($this)."' and ass.refId=".$this->id.")";
        $resultDelete = Sql::query ( $deleteArch );
        if (! $resultDelete) {
          $returnValue = Sql::$lastQueryErrorMessage;
          $returnStatus = 'ERROR';
        }
      }
      if($this->idle==1){
        $note = new Note ();
        $queryArchiv ="insert into " .$archiv->getDatabaseTableName()." ($colList) ";
        $queryArchiv.="  (select $colList from ".$hist->getDatabaseTableName()." where refType='".get_class($note)."' ";
        $queryArchiv.="    and refId in (select id from ".$note->getDatabaseTableName()." where refType='".get_class($this)."' and refId=".$this->id."))";
        $resultArch = Sql::query ( $queryArchiv );
        if (! $resultArch) {
          $returnValue = Sql::$lastQueryErrorMessage;
          $returnStatus = 'ERROR';
        }
        $deleteArch ="delete from ".$hist->getDatabaseTableName()." where refType='".get_class($note)."' ";
        $deleteArch.="  and refId in ((select id from ".$note->getDatabaseTableName()." where refType='".get_class($this)."' and refId=".$this->id."))";
        $resultDelete = Sql::query ( $deleteArch );
        if (! $resultDelete) {
          $returnValue = Sql::$lastQueryErrorMessage;
          $returnStatus = 'ERROR';
        }
      }
    }
    // Set notes on item to idle (or not)
    if (property_exists($this, 'idle') and property_exists($this, '_Note')) {
      $note = new Note ();
      $query = "update " . $note->getDatabaseTableName ();
      $query .= " set idle='" . (($this->idle)?'1':'0'). "'";
      $query .= " where refType='" . get_class ( $this ) . "' ";
      $query .= " and refId=" . $this->id;
      $result = Sql::query ( $query );
      if (! $result) {
        $returnValue = Sql::$lastQueryErrorMessage;
        $returnStatus = 'ERROR';
      }
    }
    if ($projectChange and $returnStatus != "ERROR") {
      $note = new Note ();
      $query = "update " . $note->getDatabaseTableName ();
      $query .= " set idProject='" . $this->idProject . "'";
      $query .= " where refType='" . get_class ( $this ) . "' ";
      $query .= " and refId=" . $this->id;
      $result = Sql::query ( $query );
      if (! $result) {
        $returnValue = Sql::$lastQueryErrorMessage;
        $returnStatus = 'ERROR';
      }
    }
    
    // save depedant elements (properties that are objects)
    if ($returnStatus != "ERROR" and $returnStatus != "INVALID" and ! $withoutDependencies) {
      $returnStatus = $this->saveDependantObjects ( $depedantObjects, $returnStatus );
      if ($returnStatus == "ERROR") {
        $returnValue = Sql::$lastQueryErrorMessage;
      } else if ($returnStatus == "OK") {
        $returnValue = i18n ( get_class ( $this ) ) . ' #' . htmlEncode ( $this->id ) . ' ' . i18n ( 'resultUpdated' );
      } else if ($returnStatus == "NO_CHANGE" or $returnStatus == "INCOMPLETE" or $returnStatus == "WARNING" or $returnStatus == "CONFIRM") {
        // OK
      } else if (getLastOperationStatus ( $returnStatus ) == 'INVALID') {
        return $returnStatus;
      }
    }
    if ($returnStatus == "OK") {
      $returnValue .= '${mailMsg}';
    }
    // Prepare return data
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="update" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
    return $returnValue;
  }

  /**
   * =========================================================================
   * Save the dependant objects stored in a list (may be single objects or list
   *
   * @param $depedantObjects list
   *          (array) of objects to store
   * @return void
   */
  private function saveDependantObjects($depedantObjects, $returnStatus) {
    $returnStatusDep = $returnStatus;
    foreach ( $depedantObjects as $class => $depObj ) {
      if (is_array ( $depObj ) and $returnStatusDep != "ERROR") {
        foreach ( $depObj as $depClass => $depObjOccurence ) {
          if ($depObjOccurence instanceof SqlElement and $returnStatusDep != "ERROR") {
            $depObjOccurence->refId = $this->id;
            $depObjOccurence->refType = get_class ( $this );
            
            // ADD BY Marc TABARY - 2017-03-20 - INIT REFNAME OF ORGANIZATION'S BUDGET ELEMENT
            if (get_class ( $this ) == 'Organization') {
              $depObjOccurence->refName = $this->name;
            }
            // END ADD BY Marc TABARY - 2017-03-20 - INIT REFNAME OF ORGANIZATION'S BUDGET ELEMENT
            
            $ret = $depObjOccurence->saveSqlElement ();
            if (stripos ( $ret, 'id="lastOperationStatus" value="ERROR"' )) {
              $returnStatusDep = "ERROR";
            } else if (stripos ( $ret, 'id="lastOperationStatus" value="OK"' )) {
              $returnStatusDep = 'OK';
            }
          }
        }
      } else if ($depObj instanceof SqlElement and $returnStatusDep != "ERROR") {
        $depObj->refId = $this->id;
        $depObj->refType = get_class ( $this );
        if (get_class($depObj)=='WorkElement' and ! $depObj->id) {
          $we=SqlElement::getSingleSqlElementFromCriteria('WorkElement', array('refType'=>$depObj->refType, 'refId'=>$depObj->refId));
          $depObj->id=$we->id;
        }
        // ADD BY Marc TABARY - 2017-03-20 - INIT REFNAME OF ORGANIZATION'S BUDGET ELEMENT
        if (get_class ( $this ) == 'Organization') {
          $depObj->refName = $this->name;
        }
        // END ADD BY Marc TABARY - 2017-03-20 - INIT REFNAME OF ORGANIZATION'S BUDGET ELEMENT
        
        $ret = $depObj->save ();
        if (stripos ( $ret, 'id="lastOperationStatus" value="ERROR"' )) {
          $returnStatusDep = "ERROR";
        } else if (stripos ( $ret, 'id="lastOperationStatus" value="INVALID"' )) {
          $returnStatusDep = $ret;
        } else if (stripos ( $ret, 'id="lastOperationStatus" value="OK"' )) {
          $returnStatusDep = 'OK';
        }
      }
    }
    return $returnStatusDep;
  }

  /**
   * =========================================================================
   * Delete an object from the database
   *
   * @return void
   */
  private function deleteSqlElement() {
    if (! $this->id or $this->id < 0) {
      return;
    }
    $class = get_class ( $this );
    $control = $this->deleteControl ();
    if (($control == 'OK' or strpos ( $control, 'id="confirmControl" value="delete"' ) > 0) and property_exists ( $class, $class . 'PlanningElement' )) {
      $pe = $class . 'PlanningElement';
      $controlPe = $this->$pe->deleteControl ();
      if ($controlPe != 'OK') {
        $control = $controlPe;
      }
    }
    global $adminFunctionality;
    if (isset($adminFunctionality) and $adminFunctionality=="checkConsistency") {
      $control='OK';
    }
    
    if ($control != "OK") {
      // errors on control => don't save, display error message
      if (strpos ( $control, 'id="confirmControl" value="delete"' ) > 0) {
        $returnValue = '<b>' . i18n ( 'messageConfirmationNeeded' ) . '</b><br/>' . $control;
        $returnValue .= '<input type="hidden" id="lastOperationStatus" value="CONFIRM" />';
      } else {
        $returnValue = '<b>' . i18n ( 'messageInvalidControls' ) . '</b><br/>' . $control;
        $returnValue .= '<input type="hidden" id="lastOperationStatus" value="INVALID" />';
      }
      $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
      $returnValue .= '<input type="hidden" id="lastOperation" value="control" />';
      
      return $returnValue;
    }
    foreach ( $this as $col_name => $col_value ) {
      // if property is an array containing objects, delete each
      if (is_array ( $this->$col_name )) {
        foreach ( $this->$col_name as $obj ) {
          if ($obj instanceof SqlElement) {
            if ($obj->id and $obj->id != '') { // object may be a "new" element, so try to delete only if id exists
              $obj->delete ();
            }
          }
        }
      } else if (ucfirst ( $col_name ) == $col_name) {
        // if property is an object, delete it
        if ($this->$col_name instanceof SqlElement) {
          if ($this->$col_name->id and $this->$col_name->id != '') { // object may be a "new" element, so try to delete only if id exists
            $resSub = $this->$col_name->delete ();
          }
        }
      }
    }
    // check relartionship : if "cascade", then auto delete
    $relationShip = self::$_relationShip;
    $canForceDelete = false;
    if (getSessionUser ()->id) {
      $user = getSessionUser ();
      $crit = array('idProfile' => $user->getProfile ( $this ), 'scope' => 'canForceDelete');
      $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', $crit );
      if ($habil and $habil->id and $habil->rightAccess == '1') {
        $canForceDelete = true;
      }
    }
    $returnStatus = "OK";
    $returnValue = '';
    if ($class == 'TicketSimple') {
      $class = 'Ticket';
    }
    if (array_key_exists ( $class, $relationShip )) {
      $relations = $relationShip [$class];
      $relations ['Alert'] = 'cascade';
      $relations ['IndicatorValue'] = 'cascade';
      foreach ( $relations as $object => $mode ) {
        if ($mode == "control" and $canForceDelete) {
          $mode = "confirm";
        } else if ($mode == "controlStrict") {
          $mode = "control";
        }
        if ($mode == "cascade" or ($mode == "confirm" and self::isDeleteConfirmed ())) {
          $where = null;
          $obj = new $object ();
          $crit = array($obj->getDatabaseColumnName ( 'id' . $class ) => $this->id);
          if ($class=='AccessProfileNoProject') {
            $crit = array('idAccessProfile' => $this->id);
          }
          if ($class=='ComponentVersion' and $object=='ProductAsset') {
            $crit=array($obj->getDatabaseColumnName ( 'idProductVersion' ) => $this->id);
          }
          if (property_exists ( $obj, 'refType' ) and property_exists ( $obj, 'refId' )) {
            if (property_exists ( $obj, 'id' . $class )) {
              $crit = null;
              $where = $obj->getDatabaseColumnName ( "id" . $class ) . "=" . $this->id . " or (refType='" . $class . "' and refId=" . $this->id . ")";
            } else {
              $crit = array("refType" => $class, "refId" => $this->id);
            }
          }
          if ($object == 'VersionProject' and ($class == 'ProductVersion' or $class == 'ComponentVersion')) {
            $crit = array('idVersion' => $this->id);
          }
          if ($object == "Dependency") {
            $crit = null;
            $where = "(predecessorRefType='" . $class . "' and predecessorRefId=" . Sql::fmtId ( $this->id ) . ")" . " or (successorRefType='" . $class . "' and successorRefId=" . Sql::fmtId ( $this->id ) . ")";
          }
          if ($object == "Link") {
            $crit = null;
            $where = "(ref1Type='" . $class . "' and ref1Id=" . Sql::fmtId ( $this->id ) . ")" . " or (ref2Type='" . $class . "' and ref2Id=" . Sql::fmtId ( $this->id ) . ")";
          }
          if ($object == "WorkflowStatus" and $class == 'Status') {
            $crit = null;
            $where = "idStatusFrom=" . Sql::fmtId ( $this->id ) . " or idStatusTo=" . Sql::fmtId ( $this->id );
          }
          $list = $obj->getSqlElementsFromCriteria ( $crit, false, $where );
          foreach ( $list as $subObj ) {
            $subObjDel = new $object ( $subObj->id );
            $resSub = $subObjDel->delete ();
            $statusSub = getLastOperationStatus ( $resSub );
            if ($statusSub == 'INVALID' or $statusSub == 'ERROR') {
              $returnStatus = $statusSub;
              $returnValue = $resSub;
              //$returnValue = "$object #$subObj->id <br/><br/>" . getLastOperationMessage ( $resSub );
              break ;
            }
          }
        }
      }
    }
    if ($returnStatus == "OK") { // May have errors deleting dependant elements
      $query = "delete from " . $this->getDatabaseTableName () . " where id=" . Sql::fmtId ( $this->id ) . "";
      // execute request
      $result = Sql::query ( $query );
      if (! $result) {
        $returnStatus = "ERROR";
      } else {
        $peName = get_class ( $this ) . 'PlanningElement';
        if (property_exists ( $this, $peName )) {
          $pe = new PlanningElement ();
          $pe->purge ( ' refName is null' );
        }
      }
    }
    
    // save history
    if ($returnStatus != "ERROR" and ! property_exists ( $this, '_noHistory' )) {
      $result = History::store ( $this, $class, $this->id, 'delete' );
      if (! $result) {
        $returnStatus = "ERROR";
      }
    }
    if ($returnValue == '') { // If $returnValue set from sub object, do not override with possibly empty.
      if ($returnStatus != "ERROR") {
        $returnValue = i18n ( $class ) . ' #' . htmlEncode ( $this->id ) . ' ' . i18n ( 'resultDeleted' );
      } else {
        $returnValue = Sql::$lastQueryErrorMessage;
      }
    }
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="delete" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
    $returnValue .= '<input type="hidden" id="noDataMessage" value="' . str_replace('"',"'",htmlGetNoDataMessage(get_class($this))). '" />';
    return $returnValue;
  }

  /**
   * =========================================================================
   * Purge objects from the database : delete all objects corresponding
   * to clause $ clause
   * Important :
   * => does not automatically purges included elements ...
   * => does not include history insertion
   *
   * @return void
   */
  private function purgeSqlElement($clause) {
    $objectClass = get_class ( $this );
    // purge depending Planning Element if any
    if (property_exists ( $this, $objectClass . 'PlanningElement' )) {
      $query = "select id from " . $this->getDatabaseTableName () . " where " . $clause;
      $resultId = Sql::query ( $query );
      if (Sql::$lastQueryNbRows > 0) {
        $line = Sql::fetchLine ( $resultId );
        $peCrit = '(0';
        while ( $line ) {
          $peCrit .= ',' . $line ['id'];
          $line = Sql::fetchLine ( $resultId );
        }
        $peCrit .= ')';
        $pe = new PlanningElement ();
        $query = "delete from " . $pe->getDatabaseTableName () . " where refType='$objectClass' and refId in $peCrit";
        Sql::query ( $query );
      }
    }
    // get all data, and identify if changes
    $query = "delete from " . $this->getDatabaseTableName () . " where " . $clause;
    // execute request
    $returnStatus = "OK";
    $result = Sql::query ( $query );
    if (! $result) {
      $returnStatus = "ERROR";
    }
    if ($returnStatus != "ERROR") {
      $returnValue = Sql::$lastQueryNbRows . " " . i18n ( get_class ( $this ) ) . '(s) ' . i18n ( 'doneoperationdelete' );
    } else {
      $returnValue = Sql::$lastQueryErrorMessage;
    }
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="delete" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
    $returnValue .= '<input type="hidden" id="noDataMessage" value="' . htmlGetNoDataMessage ( get_class ( $this ) ) . '" />';
    return $returnValue;
  }

  /**
   * =========================================================================
   * Close objects from the database : delete all objects corresponding
   * to clause $ clause
   * Important :
   * => does not automatically purges included elements ...
   * => does not include history insertion
   *
   * @return void
   */
  private function closeSqlElement($clause) {
    $objectClass = get_class ( $this );
    // get all data, and identify if changes
    $query = "update " . $this->getDatabaseTableName () . " set idle='1' where " . $clause;
    // execute request
    $returnStatus = "OK";
    $result = Sql::query ( $query );
    if (! $result) {
      $returnStatus = "ERROR";
    }
    if ($returnStatus != "ERROR") {
      $returnValue = Sql::$lastQueryNbRows . " " . i18n ( get_class ( $this ) ) . '(s) ' . i18n ( 'doneoperationclose' );
    } else {
      $returnValue = Sql::$lastQueryErrorMessage;
    }
    $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $this->id ) . '" />';
    $returnValue .= '<input type="hidden" id="lastOperation" value="update" />';
    $returnValue .= '<input type="hidden" id="lastOperationStatus" value="' . $returnStatus . '" />';
    $returnValue .= '<input type="hidden" id="noDataMessage" value="' . htmlGetNoDataMessage ( get_class ( $this ) ) . '" />';
    return $returnValue;
  }

  /**
   * =========================================================================
   * Copy the curent object as a new one of the same class
   *
   * @return the new object
   */
  private function copySqlElement() {
    $newObj = clone $this;
    $newObj->id = null;
    if (property_exists ( $newObj, "wbs" )) {
      $newObj->wbs = null;
    }
    if (property_exists ( $newObj, "topId" )) {
      $newObj->topId = null;
    }
    if (property_exists ( $newObj, "idStatus" )) {
      if (get_class ( $newObj ) == 'TestSession') {
        $list = SqlList::getList ( 'Status' );
        $revert = array_keys ( $list );
        $newObj->idStatus = $revert [0];
      } else {
        $st = SqlElement::getSingleSqlElementFromCriteria ( 'Status', array('isCopyStatus' => '1') );
        if (! $st or ! $st->id) {
          errorLog ( "Error : several or no status exist with isCopyStatus=1 (expected is 1 only and only 1)" );
        }
        $newObj->idStatus = $st->id;
      }
    }
    if (property_exists ( $newObj, "idUser" ) and get_class ( $newObj ) != 'Affectation' and get_class ( $newObj ) != 'Message') {
      $newObj->idUser = getSessionUser ()->id;
    }
    if (property_exists ( $newObj, "creationDate" )) {
      $newObj->creationDate = date ( 'Y-m-d' );
    }
    if (property_exists ( $newObj, "creationDateTime" )) {
      $newObj->creationDateTime = date ( 'Y-m-d H:i' );
    }
    if (property_exists ( $newObj, "lastUpdateDateTime" )) {
      $newObj->lastUpdateDateTime=null;
    }
    if (property_exists ( $newObj, "done" )) {
      $newObj->done = 0;
    }
    if (property_exists ( $newObj, "idle" )) {
      $newObj->idle = 0;
    }
    if (property_exists ( $newObj, "idleDate" )) {
      $newObj->idleDate = null;
    }
    if (property_exists ( $newObj, "doneDate" )) {
      $newObj->doneDate = null;
    }
    if (property_exists ( $newObj, "idleDateTime" )) {
      $newObj->idleDateTime = null;
    }
    if (property_exists ( $newObj, "doneDateTime" )) {
      $newObj->doneDateTime = null;
    }
    if (property_exists ( $newObj, "reference" )) {
      $newObj->reference = null;
    }
    if (property_exists ( $newObj, "password" )) {
      $newObj->password = null;
    }
    if (property_exists ( $newObj, "apiKey" )) {
      $newObj->apiKey = md5 ( $this->id . date ( 'Ymdhis' ) );
    }
    if (property_exists ( $newObj, "idRunStatus" )) {
      $newObj->idRunStatus = 5;
    }
    if (property_exists ( $newObj, "idSituation" )) {
    	$newObj->idSituation = null;
    }
    foreach ( $newObj as $col_name => $col_value ) {
      if (ucfirst ( $col_name ) == $col_name) {
        // if property is an object, delete it
        if ($newObj->$col_name instanceof SqlElement) {
          $newObj->$col_name->id = null;
          if (property_exists ( $newObj->$col_name, "wbs" )) {
            $newObj->$col_name->wbs = null;
          }
          if (property_exists ( $newObj->$col_name, "topId" )) {
            $newObj->$col_name->topId = null;
          }
          if ($newObj->$col_name instanceof PlanningElement) {
            $newObj->$col_name->plannedStartDate = "";
            $newObj->$col_name->realStartDate = "";
            $newObj->$col_name->plannedEndDate = "";
            $newObj->$col_name->realEndDate = "";
            $newObj->$col_name->plannedDuration = "";
            $newObj->$col_name->realDuration = "";
            $newObj->$col_name->assignedWork = 0;
            $newObj->$col_name->plannedWork = 0;
            $newObj->$col_name->leftWork = 0;
            $newObj->$col_name->realWork = 0;
            $newObj->$col_name->notPlannedWork = 0;
            $newObj->$col_name->idle = 0;
            $newObj->$col_name->done = 0;
          }
        }
      }
    }
    if (get_class ( $this ) == 'User') {
      $newObj->name = i18n ( 'copiedFrom' ) . ' ' . $newObj->name;
      if ($newObj->resourceName) {
        $newObj->resourceName = i18n ( 'copiedFrom' ) . ' ' . $newObj->resourceName;
      }
    } else if ( get_class($newObj)=='Resource' or get_class($newObj)=='Contact' or get_class($newObj)=='Employee') {
      $newObj->name = i18n ( 'copiedFrom' ) . ' ' . $newObj->name;
      if ($newObj->userName) {
        $newObj->userName = i18n ( 'copiedFrom' ) . ' ' . $newObj->userName;
      }
    }
    if (is_a ( $this, 'Version' ) and $newObj->versionNumber) {
      $existWithName = $newObj->countSqlElementsFromCriteria ( null, "name='".Sql::fmtStr($newObj->name)."'" );
      if ($existWithName > 0) {
        $newObj->versionNumber = $newObj->versionNumber . ' (' . i18n ( 'copy' ) . ')';
        $newObj->name = $newObj->name . ' (' . i18n ( 'copy' ) . ')';
      }
    }
    if (property_exists ( $newObj, "isCopyStatus" )) {
      $newObj->isCopyStatus = 0;
    }
    $result = $newObj->save();
    Sql::$lastCopyId = $newObj->id;
    if (stripos ( $result, 'id="lastOperationStatus" value="OK"' ) > 0) {
      $returnValue = i18n ( get_class ( $this ) ) . ' #' . htmlEncode ( $this->id ) . ' ' . i18n ( 'resultCopied' ) . ' #' . $newObj->id;
      $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $newObj->id ) . '" />';
      $returnValue .= '<input type="hidden" id="lastOperation" value="copy" />';
      $returnValue .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
    } else {
      $returnValue = $result;
    }
    $newObj->_copyResult = $returnValue;
    return $newObj;
  }

  private function copySqlElementTo($newClass, $newType, $newName, $newProject, $setOrigin, $withNotes, $withAttachments, $withLinks, $withAssignments = false, $withAffectations = false, $toProject = null, $toActivity = null, $copyToWithResult = false) {
    $newObj = new $newClass ();
    $newObj->id = null;
    //$typeName = 'id' . $newClass . 'Type';
    ($newClass=='PeriodicMeeting')?$typeName='idMeetingType':$typeName = 'id' . $newClass . 'Type';
    $typeClass=substr($typeName,2);
    if($newClass!="CatalogUO"){
      $newObj->$typeName = $newType;
    }
    if ($setOrigin and property_exists ( $newObj, 'Origin' )) {
      $newObj->Origin->originType = get_class ( $this );
      $newObj->Origin->originId = $this->id;
      $newObj->Origin->refType = $newClass;
    }
    foreach ($newObj as $col_name => $col_value ) {
      if (ucfirst ( $col_name ) == $col_name) {
        if ($newObj->$col_name instanceof PlanningElement) {
          $sub = substr ( $col_name, 0, strlen ( $col_name ) - 15 );
          $plMode = 'id' . $sub . 'PlanningMode';
          $pm=null;
          if (get_class($this)==$newClass and $newClass != 'Project') {
            $pm = $this->$col_name->$plMode;
          }else if ($newClass != 'Project') {  
            $t=new $typeClass($newType);
            if (property_exists($t, $plMode)) {
              $pm=$t->$plMode;
            } else {
              $lst=SqlList::getList(substr($plMode,2));
              if (count($lst)>0) {
                foreach ($lst as $id=>$val) {
                  $pm=$id;
                  break;
                }
              }
            }
            if (property_exists($newObj, 'idPlanningMode')) $newObj->idPlanningMode=$pm;
            $newObj->$col_name->$plMode = $pm;
          }
          $newObj->$col_name->refName = $newName;
        }
      }
    }
    foreach ( $this as $col_name => $col_value ) {
      if (ucfirst ( $col_name ) == $col_name) {
        if ($this->$col_name instanceof SqlElement) {
          // $newObj->$col_name->id=null;
          if ($this->$col_name instanceof PlanningElement) {
            $pe = $newClass . 'PlanningElement';
            if (property_exists ( $newObj, $pe )) {
              if (get_class ( $this ) == $newClass) {
                $plMode = 'id' . $newClass . 'PlanningMode';
                if (property_exists ( $this->$col_name, $plMode )) {
                  $newObj->$col_name->$plMode = $this->$col_name->$plMode;
                }
              }
              $newObj->$pe->initialStartDate = $this->$col_name->initialStartDate;
              $newObj->$pe->initialEndDate = $this->$col_name->initialEndDate;
              $newObj->$pe->initialDuration = $this->$col_name->initialDuration;
              $newObj->$pe->validatedStartDate = $this->$col_name->validatedStartDate;
              $newObj->$pe->validatedEndDate = $this->$col_name->validatedEndDate;
              $newObj->$pe->validatedDuration = $this->$col_name->validatedDuration;
              $newObj->$pe->validatedWork = $this->$col_name->validatedWork;
              $newObj->$pe->validatedCost = $this->$col_name->validatedCost;
              $newObj->$pe->priority = $this->$col_name->priority;
              // $newObj->$pe->topId=$this->$col_name->topId;
              $newObj->$pe->topRefType = $this->$col_name->topRefType;
              $newObj->$pe->topRefId = $this->$col_name->topRefId;
            }
          }
          
          // ADD BY Marc TABARY - 2017-02-09
          if ($this->$col_name instanceof BudgetElement) {
            $newObj->$col_name->budgetWork = 0;
            $newObj->$col_name->validatedWork = 0;
            $newObj->$col_name->assignedWork = 0;
            $newObj->$col_name->realWork = 0;
            $newObj->$col_name->leftWork = 0;
            $newObj->$col_name->plannedWork = 0;
            $newObj->$col_name->budgetCost = null;
            $newObj->$col_name->validatedCost = null;
            $newObj->$col_name->assignedCost = null;
            $newObj->$col_name->realCost = null;
            $newObj->$col_name->leftCost = null;
            $newObj->$col_name->plannedCost = null;
            $newObj->$col_name->progress = 0;
            $newObj->$col_name->expenseBudgetAmount = null;
            $newObj->$col_name->expenseAssignedAmount = null;
            $newObj->$col_name->expensePlannedAmount = null;
            $newObj->$col_name->expenseRealAmount = null;
            $newObj->$col_name->expenseLeftAmount = null;
            $newObj->$col_name->expenseValidatedAmount = null;
            $newObj->$col_name->totalBudgetCost = null;
            $newObj->$col_name->totalAssignedCost = null;
            $newObj->$col_name->totalPlannedCost = null;
            $newObj->$col_name->totalRealCost = null;
            $newObj->$col_name->totalLeftCost = null;
            $newObj->$col_name->totalValidatedCost = null;
            $newObj->$col_name->reserveAmount = null;
            $newObj->$col_name->idle = 0;
            // ADD BY Marc TABARY - 2017-03-09 - PERIODIC YEAR BUDGET ELEMENT
            $newObj->$col_name->idleDateTime = null;
            // END ADD BY Marc TABARY - 2017-03-09 - PERIODIC YEAR BUDGET ELEMENT
          }
          // END ADD BY Marc TABARY - 2017-02-09
        }
      } else if (property_exists ( $newObj, $col_name )) {
        if ($col_name != 'id' and $col_name != "wbs" and $col_name != 'name' and $col_name != $typeName and $col_name != "handled" and $col_name != "handledDate" and $col_name != "handledDateTime" and $col_name != "done" and $col_name != "doneDate" and $col_name != "doneDateTime" and $col_name != "idle" and $col_name != "idleDate" and $col_name != "idelDateTime" and $col_name != "idStatus" and $col_name != "reference" and $col_name != "billId") { // topId ?
          $newObj->$col_name = $this->$col_name;
        }
      }
    }
    if (property_exists ( $newObj, "idStatus" )) {
      $st = SqlElement::getSingleSqlElementFromCriteria ( 'Status', array('isCopyStatus' => '1') );
      if (! $st or ! $st->id) {
        errorLog ( "Error : several on no status exist with isCopyStatus=1" );
      }
      $newObj->idStatus = $st->id;
    }
    if (property_exists ( $newObj, "idUser" ) and get_class ( $newObj ) != 'Affectation' and get_class ( $newObj ) != 'Message') {
      $newObj->idUser = getSessionUser ()->id;
    }
    if (property_exists ( $newObj, "paymentAmount" )) {
      $newObj->paymentAmount = null;
    }
    if (property_exists ( $newObj, "paymentDate" )) {
      $newObj->paymentDate = null;
    }
    if (property_exists ( $newObj, "creationDate" )) {
      $newObj->creationDate = date ( 'Y-m-d' );
    }
    if (property_exists ( $newObj, "creationDateTime" )) {
      $newObj->creationDateTime = date ( 'Y-m-d H:i' );
    }
    if (property_exists ( $newObj, "lastUpdateDateTime" )) {
      $newObj->lastUpdateDateTime=null;
    }
    if (property_exists ( $newObj, "meetingDate" )) {
      $newObj->meetingDate = date ( 'Y-m-d' );
    }
    if (property_exists ( $newObj, "reference" )) {
      $newObj->reference = null;
    }
    if (property_exists ( $newObj, "idProject" ) and $toProject) {
      $newObj->idProject = $toProject;
    }
    if (property_exists ( $newObj, "idActivity" ) and $toActivity) {
      $newObj->idActivity = $toActivity;
    }
    if (property_exists ( $newObj, "fixPerimeter" )) {
      $newObj->fixPerimeter = 0;
    }
    //gautier #4404
    if (get_class ( $newObj ) == 'Asset') {
      $newObj->serialNumber = null;
      $newObj->inventoryNumber = null;
      $newObj->installationDate = null;
      $newObj->decommissioningDate = null;
    }
    if (get_class ( $newObj ) == 'Bill') {
      $newObj->paymentDate = null;
      $newObj->paymentAmount = null;
      $newObj->paymentDone = null;
      $newObj->paymentsCount = null;
      $newObj->date = date ( 'Y-m-d' );
      $newObj->sendDate = null;
      $newObj->idDeliveryMode = null;
    }
    if (property_exists($newObj, "sendDate")) {
    	$newObj->sendDate = null;
    }
    if (property_exists($newObj, "paymentDate")) {
      $newObj->paymentDate = null;
    }
    if (property_exists($newObj, "paymentAmount")) {
      $newObj->paymentAmount = null;
    }
    if (property_exists($newObj, "paymentDone")) {
      $newObj->paymentDone = null;
    }
    if (property_exists($newObj, "paymentsCount")) {
      $newObj->paymentsCount = null;
    }
    if (property_exists($newObj, "checked")) {
      $newObj->checked = null;
    }
    
    if (get_class ( $newObj ) == 'ProviderPayment') {
      $newObj->idPaymentMode = SqlList::getFirstId('PaymentMode');
      $newObj->paymentDate=date('Y-m-d');
      if (property_exists($this, 'totalFullAmount')) {
        $newObj->paymentAmount=$this->totalFullAmount;
      } else if (property_exists($this, 'fullAmount')) {
        $newObj->paymentAmount=$this->fullAmount;
      }
      if (get_class($this)=='ProviderTerm') {
        $newObj->idProviderTerm=$this->id;
      } else if (get_class($this)=='ProviderBill') {
        $newObj->idProviderBill=$this->id;
      }
    }
    $newObj->name = $newName;
    if (! $toProject and property_exists($newObj, 'idProject') and $newProject) {
      $newObj->idProject=$newProject;
      if ($newObj->idProject!=$this->idProject and property_exists($newObj, 'idActivity')) {
        $pAct=new Activity($newObj->idActivity,true);
        if ($newObj->idProject!=$pAct->idProject) $newObj->idActivity=null;
      } 
    }
    // check description
    if (property_exists ( $newObj, 'description' ) and ! $newObj->description) {
      $idType = 'id' . $newClass . 'Type';
      if (property_exists ( $newObj, $idType )) {
        $type = $newClass . 'Type';
        $objType = new $type ( $newObj->$idType );
        if (property_exists ( $objType, 'mandatoryDescription' ) and $objType->mandatoryDescription) {
          $newObj->description = $newObj->name;
        }
      }
    }
    if (! $copyToWithResult and property_exists ( $newObj, "result" )) {
      $newObj->result = null;
    }
    $result = $newObj->save ();
    if (stripos ( $result, 'id="lastOperationStatus" value="OK"' ) > 0) {
      $returnValue = i18n ( get_class ( $this ) ) . ' #' . htmlEncode ( $this->id ) . ' ' . i18n ( 'resultCopied' ) . ' #' . $newObj->id;
      $returnValue .= '<input type="hidden" id="lastSaveId" value="' . htmlEncode ( $newObj->id ) . '" />';
      $returnValue .= '<input type="hidden" id="lastOperation" value="copy" />';
      $returnValue .= '<input type="hidden" id="lastOperationStatus" value="OK" />';
    } else {
      $returnValue = $result;
    }
    if ($withNotes and property_exists ( $this, "_Note" ) and property_exists ( $newObj, "_Note" )) {
      $crit = array('refType' => get_class ( $this ), 'refId' => $this->id);
      $note = new Note ();
      $notes = $note->getSqlElementsFromCriteria ( $crit );
      foreach ( $notes as $note ) {
        $note->id = null;
        $note->refType = get_class ( $newObj );
        $note->refId = $newObj->id;
        $note->save ();
      }
    }
    
    if ($withLinks) {
      $crit = array('ref1Type' => get_class ( $this ), 'ref1Id' => $this->id);
      $link = new Link ();
      $links = $link->getSqlElementsFromCriteria ( $crit );
      foreach ( $links as $link ) {
        $link->id = null;
        $link->ref1Type = get_class ( $newObj );
        $link->ref1Id = $newObj->id;
        $link->save ();
      }
      $crit = array('ref2Type' => get_class ( $this ), 'ref2Id' => $this->id);
      $link = new Link ();
      $links = $link->getSqlElementsFromCriteria ( $crit );
      foreach ( $links as $link ) {
        $link->id = null;
        $link->ref2Type = get_class ( $newObj );
        $link->ref2Id = $newObj->id;
        $link->save ();
      }
    }
    if ($withAttachments) {
      $crit = array('refType' => get_class ( $this ), 'refId' => $this->id);
      $attachment = new Attachment ();
      $attachments = $attachment->getSqlElementsFromCriteria ( $crit );
      $pathSeparator = Parameter::getGlobalParameter ( 'paramPathSeparator' );
      $attachmentDirectory = Parameter::getGlobalParameter ( 'paramAttachmentDirectory' );
      foreach ( $attachments as $attachment ) {
        $fromdir = $attachmentDirectory . $pathSeparator . "attachment_" . $attachment->id . $pathSeparator;
        if (file_exists ( $fromdir . $attachment->fileName ) or $attachment->type=='link') {
          $attachment->id = null;
          $attachment->refType = get_class ( $newObj );
          $attachment->refId = $newObj->id;
          $attachment->save ();
          if ($attachment->type=='file') {
            $todir = $attachmentDirectory . $pathSeparator . "attachment_" . $attachment->id . $pathSeparator;
            if (! file_exists ( $todir )) {
              mkdir ( $todir, 0777, true );
            }
            copy ( $fromdir . $attachment->fileName, $todir . $attachment->fileName );
            $attachment->subDirectory = str_replace ( $attachmentDirectory, '${attachmentDirectory}', $todir );
            $attachment->save ();
          }
        }
      }
    }
    if ($withAssignments and property_exists ( $this, "_Assignment" ) and property_exists ( $newObj, "_Assignment" )) {
      $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', array(
          'idProfile' => getSessionUser ()->getProfile ( $this ), 
          'scope' => 'assignmentEdit') );
      if ($habil and $habil->rightAccess == 1) {
        $ass = new Assignment ();
        // First delete existing Assignment (possibly created from Responsible)
        if (property_exists ( $this, 'idResource' ) and $this->idResource) {
          $crit = array('idResource' => $this->idResource, 'refType' => get_class ( $this ), 'refId' => $newObj->id);
          $assResp = SqlElement::getSingleSqlElementFromCriteria ( 'Assignment', $crit );
          if ($assResp and $assResp->id) {
            $resDel = $assResp->delete ();
          }
        }
        $crit = array('refType' => get_class ( $this ), 'refId' => $this->id);
        $lstAss = $ass->getSqlElementsFromCriteria ( $crit );
        foreach ( $lstAss as $ass ) {
          if ($ass->supportedResource) continue; // Do not copy support assignment, will be generated automatically
          $oldAssId=$ass->id;
          $ass->id = null;
          $ass->idProject = $newObj->idProject;
          $ass->refType = $newClass;
          $ass->refId = $newObj->id;
          $ass->comment = null;
          $ass->realWork = 0;
          $planningName = get_class ( $this ).'PlanningElement';
          if($this->$planningName->idPlanningMode){
            $planningMode = new PlanningMode($this->$planningName->idPlanningMode);
          }
          if($planningMode and $planningMode->code=='MAN'){
            $ass->assignedWork = null;
            $ass->leftWork = null;
            $ass->plannedWork = null;
          }else{
            $ass->leftWork = $ass->assignedWork;
            $ass->plannedWork = $ass->assignedWork;
          }
          $ass->realStartDate = null;
          $ass->realEndDate = null;
          $ass->plannedStartDate = null;
          $ass->plannedEndDate = null;
          $ass->realCost = 0;
          $ass->leftCost = $ass->assignedCost;
          $ass->plannedCost = $ass->assignedCost;
          $ass->billedWork = null;
          $ass->idle = 0;
          $ass->save ();
          // Also copy AssignmentRecurring for recurring tasks
          $assRec=new AssignmentRecurring();
          $critRec = array('idAssignment' => $oldAssId);
          $lstAssRec = $assRec->getSqlElementsFromCriteria ( $critRec );
          foreach ( $lstAssRec as $assRec ) {
            $assRec->id = null;
            $assRec->idAssignment=$ass->id;
            $assRec->refType = $newClass;
            $assRec->refId = $newObj->id;
            $resAssRec=$assRec->save();
          }
          // Also copy AssignmentSelection for recurring tasks
          $assSel=new AssignmentSelection();
          $critSel = array('idAssignment' => $oldAssId);
          $lstAssSel = $assSel->getSqlElementsFromCriteria ( $critSel );
          foreach ( $lstAssSel as $assSel ) {
            $assSel->id = null;
            $assSel->idAssignment=$ass->id;
            $assSel->startDate=null;
            $assSel->endDate=null;
            $assSel->userSelected=0;
            $assSel->selected=0;
            $resAssSel=$assSel->save();
          }
        }
      }
    }
    if (property_exists ( $this, '_BillLine' ) and property_exists ( $this, '_BillLine' )) { // Copy BillLines
      $crit = array('refType' => get_class ( $this ), 'refId' => $this->id);
      $line = new BillLine ();
      $lines = $line->getSqlElementsFromCriteria ( $crit );
      foreach ( $lines as $line ) {
        $line->id = null;
        $line->refType = get_class ( $newObj );
        $line->refId = $newObj->id;
        $line->save ();
      }
    }    
    $newObj->_copyResult = $returnValue;
    return $newObj;
  }
  
  // ============================================================================**********
  // GET AND FETCH OBJECTS FUNCTIONS
  // ============================================================================**********
  
  /**
   * =========================================================================
   * Retrieve an object from the Request (modified Form) - Public method
   *
   * @return void (operate directly on the object)
   */
  public function fillFromRequest($ext = null) {
    $this->fillSqlElementFromRequest ( null, $ext );
  }

  /**
   * ========================================================================
   * Retrieve a list of objects from the Database
   * Called from an empty object of the expected class
   *
   * @param array $critArray
   *          the critera as an array
   * @param boolean $initializeIfEmpty
   *          indicating if no result returns an
   *          initialised element or not
   * @param string $clauseWhere
   *          Sql Where clause (alternative way to define criteria)
   *          => $critArray must not be set
   * @param string $clauseOrderBy
   *          Sql Order By clause
   * @param boolean $getIdInKey          
   * @return SqlElement[] an array of objects
   */
  public function getSqlElementsFromCriteria($critArray, $initializeIfEmpty = false, $clauseWhere = null, $clauseOrderBy = null, $getIdInKey = false, $withoutDependentObjects = false, $maxElements = null) {
    // scriptLog("getSqlElementsFromCriteria(implode('|',$critArray), $initializeIfEmpty,$clauseWhere, $clauseOrderBy, $getIdInKey)");
    // Build where clause from criteria
    global $globalSilentErrors;
    $whereClause = '';
    $objects = array();
    $className = get_class ( $this );
    $defaultObj = new $className ();
    if ($critArray) {
      foreach ( $critArray as $colCrit => $valCrit ) {
        $whereClause .= ($whereClause == '') ? ' where ' : ' and ';
        if ($valCrit == null or $valCrit == ' ') {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . ' is null';
        } else {
          if ($this->getDataType ( $colCrit ) == 'int' and is_numeric ( $valCrit )) {
            $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . '=' . $valCrit;
          } else {
            $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . '=' . Sql::str ( $valCrit );
          }
        }
        $defaultObj->$colCrit = $valCrit;
      }
    } else if ($clauseWhere) {
      $whereClause = ' where ' . $clauseWhere;
    }
    $objectCrit = $this->getDatabaseCriteria ();
    if (count ( $objectCrit ) > 0) {
      foreach ( $objectCrit as $colCrit => $valCrit ) {
        $whereClause .= ($whereClause == '') ? ' where ' : ' and ';
        $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . " = " . Sql::str ( $valCrit ) . " ";
      }
    }
    if (property_exists ( $this, 'isPrivate' )) {
      $whereClause .= ($whereClause == '') ? ' where ' : ' and ';
      $whereClause .= SqlElement::getPrivacyClause ( $this );
    }
    if (array_key_exists ( $className, self::$_cachedQuery )) {
      if (array_key_exists ( $whereClause, self::$_cachedQuery [$className] )) {
        return self::$_cachedQuery [$className] [$whereClause];
      }
    }
    // If $whereClause is set, get the element from Database
    $query = 'select * from ' . $this->getDatabaseTableName () . $whereClause;
    if ($clauseOrderBy) {
      $query .= ' order by ' . $clauseOrderBy;
    } else if (isset ( $this->sortOrder )) {
      $query .= ' order by ' . $this->getDatabaseTableName () . '.sortOrder';
    }
    if ($maxElements) {
      $query .= ' LIMIT ' . $maxElements;
    }
    $result = Sql::query ( $query );
    
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine ( $result );
      while ( $line ) {
        $obj = clone ($this);
        // get all data fetched
        $keyId = null;
        foreach ( $obj as $col_name => $col_value ) {
          if (substr ( $col_name, 0, 1 ) == "_") {
            // not a field, just for presentation purpose
          } else if (strpos ( $this->getFieldAttributes ( $col_name ), 'calculated' ) !== false) {
            // calculated field : not to be fetched
          } else if (ucfirst ( $col_name ) == $col_name) {
            if (! $withoutDependentObjects) {
              $obj->$col_name = $obj->getDependantSqlElement ( $col_name );
            }
          } else {
            $dbColName = $obj->getDatabaseColumnName ( $col_name );
            if (array_key_exists ( $dbColName, $line )) {
              $obj->{$col_name} = $line [$dbColName];
            } else if (array_key_exists ( strtolower ( $dbColName ), $line )) {
              $obj->{$col_name} = $line [strtolower ( $dbColName )];
            } else {
              if (! $globalSilentErrors)
                errorLog ( "Error on SqlElement to get '" . $col_name . "' for Class '" . get_class ( $obj ) . "' " . " : field '" . $dbColName . "' not found in Database." );
            }
            if ($col_name == 'id' and $getIdInKey) {
              $keyId = '#' . $obj->{$col_name};
            }
          }
          // FOR PHP 7.1 COMPATIBILITY
          $dataType=$obj->getDataType($col_name);
          $dataLength=$obj->getDataLength($col_name);
          if ($obj->{$col_name}===null 
          and ( ($dataType=='decimal') or ($dataType=='numeric') ) 
          and $col_name!='warningValue' and $col_name!='alertValue'
//ELIOTT - LEAVE SYSTEM
          and !(in_array(get_class($obj),self::$_classesArrayToBypassPHPCompatibilityIf)  and in_array($col_name,self::$_attributesArrayToBypassPHPCompatibilityIf)) ) {
//ELIOTT - LEAVE SYSTEM
            $obj->{$col_name}=0;
          }
        }
        if ($getIdInKey) {
          $objects [$keyId] = $obj;
        } else {
          $objects [] = $obj;
        }
        
        $line = Sql::fetchLine ( $result );
      }
    } else {
      if ($initializeIfEmpty) {
        $objects [] = $defaultObj; // return at least 1 element, initialized with criteria
      }
    }
    if (array_key_exists ( $className, self::$_cachedQuery )) {
      self::$_cachedQuery [$className] [$whereClause] = $objects;
    }
    return $objects;
  }

  /**
   * ========================================================================
   * Retrieve the count of a list of objects from the Database
   * Called from an empty object of the expected class
   *
   * @param $critArray the
   *          critera asd an array
   * @param $clauseWhere Sql
   *          Where clause (alternative way to define criteria)
   *          => $critArray must not be set
   * @param $clauseOrderBy Sql
   *          Order By clause
   * @return an array of objects
   */
  public function countSqlElementsFromCriteria($critArray, $clauseWhere = null) {
    // Build where clause from criteria
    $whereClause = '';
    $objects = array();
    $className = get_class ( $this );
    $defaultObj = new $className ();
    if ($critArray) {
      foreach ( $critArray as $colCrit => $valCrit ) {
        $whereClause .= ($whereClause == '') ? ' where ' : ' and ';
        if ($valCrit == null) {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . ' is null';
        } else {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . '= ' . Sql::str ( $valCrit );
        }
        $defaultObj->$colCrit = $valCrit;
      }
    } else if ($clauseWhere) {
      $whereClause = ' where ' . $clauseWhere;
    }
    // If $whereClause is set, get the element from Database
    $query = "select count(*) as cpt from " . $this->getDatabaseTableName () . $whereClause;
    $result = Sql::query ( $query );
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine ( $result );
      return $line ['cpt'];
    }
    return 0;
  }

  public function getMaxValueFromCriteria($field, $critArray, $clauseWhere = null) {
    $whereClause = '';
    $objects = array();
    $className = get_class ( $this );
    $defaultObj = new $className ();
    if ($critArray) {
      foreach ( $critArray as $colCrit => $valCrit ) {
        $whereClause .= ($whereClause == '') ? ' where ' : ' and ';
        if ($valCrit == null) {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . ' is null';
        } else {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . '= ' . Sql::str ( $valCrit );
        }
        $defaultObj->$colCrit = $valCrit;
      }
    } else if ($clauseWhere) {
      $whereClause = ' where ' . $clauseWhere;
    }
    // If $whereClause is set, get the element from Database
    $query = "select max($field) as value from " . $this->getDatabaseTableName () . $whereClause;
    $result = Sql::query ( $query );
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine ( $result );
      return $line ['value'];
    }
    return null;
  }
  public function getMinValueFromCriteria($field, $critArray, $clauseWhere = null, $excludeNull=false) {
    $whereClause = '';
    $objects = array();
    $className = get_class ( $this );
    $defaultObj = new $className ();
    if ($critArray) {
      foreach ( $critArray as $colCrit => $valCrit ) {
        $whereClause .= ($whereClause == '') ? ' where ' : ' and ';
        if ($valCrit == null) {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . ' is null';
        } else {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . '= ' . Sql::str ( $valCrit );
        }
        $defaultObj->$colCrit = $valCrit;
      }
    } else if ($clauseWhere) {
      $whereClause = ' where ' . $clauseWhere;
    }
    if ($excludeNull) {
      $whereClause .= " and $field is not null";
    }
    // If $whereClause is set, get the element from Database
    $query = "select min($field) as value from " . $this->getDatabaseTableName () . $whereClause;
    $result = Sql::query ( $query );
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine ( $result );
      return $line ['value'];
    }
    return null;
  }
  public function sumSqlElementsFromCriteria($field, $critArray, $clauseWhere = null, $grouped=null) {
    // Build where clause from criteria
    $fields = array();
    if (is_array ( $field )) {
      $fields = $field;
    } else {
      $fields = array($field);
    }
    $whereClause = '';
    $objects = array();
    $className = get_class ( $this );
    $defaultObj = new $className ();
    if ($critArray) {
      foreach ( $critArray as $colCrit => $valCrit ) {
        $whereClause .= ($whereClause == '') ? ' where ' : ' and ';
        if ($valCrit == null) {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . ' is null';
        } else {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . '= ' . Sql::str ( $valCrit );
        }
        $defaultObj->$colCrit = $valCrit;
      }
    } else if ($clauseWhere) {
      $whereClause = ' where ' . $clauseWhere;
    }
    // If $whereClause is set, get the element from Database
    $selectFields = "";
    foreach ( $fields as $fld ) {
      if ($selectFields)
        $selectFields .= ', ';
      $fldName = $defaultObj->getDatabaseColumnName ( $fld );
      $selectFields .= " sum($fldName) as sum" . strtolower ( $fld );
    }
    
    $query = "select " . $selectFields . (($grouped)?','.$grouped:'').' from ' . $this->getDatabaseTableName () . $whereClause.(($grouped)?' group by '.$grouped:'');
    $result = Sql::query ( $query );
    if (Sql::$lastQueryNbRows > 0) {
      $line = Sql::fetchLine ( $result );
      if ($grouped) {
        $res=array();
        while ($line) {
          $resLine=array();
          foreach($line as $key=>$val) {
            $resLine[strtolower($key)]=$val;
          }
          $res[]=$resLine;
          $line = Sql::fetchLine ( $result );
        }
        return $res;
      } else if (is_array ( $field )) {
        return $line;
      } else {
        return $line ["sum" . strtolower ( $field )];
      }
    }
    return null;
  }

  public function countGroupedSqlElementsFromCriteria($critArray, $critGroup, $critwhere) {
    // Build where clause from criteria
    $whereClause = '';
    $className = get_class ( $this );
    if ($critArray) {
      foreach ( $critArray as $colCrit => $valCrit ) {
        $whereClause .= ($whereClause == '') ? ' where ' : ' and ';
        if ($valCrit == null) {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . ' is null';
        } else {
          $whereClause .= $this->getDatabaseTableName () . '.' . $this->getDatabaseColumnName ( $colCrit ) . '= ' . Sql::str ( $valCrit );
        }
      }
    } else {
      $whereClause = $critwhere;
    }
    $groupList = '';
    $critGroup = array_map ( 'strtolower', $critGroup );
    foreach ( $critGroup as $group ) {
      $groupList .= ($groupList == '') ? '' : ', ';
      $groupList .= $group;
    }
    $query = "select $groupList, count(*) as cpt from " . $this->getDatabaseTableName () . ' where ' . $whereClause . " group by $groupList";
    $result = Sql::query ( $query );
    $groupRes = array();
    if (Sql::$lastQueryNbRows > 0) {
      while ( $line = Sql::fetchLine ( $result ) ) {
        $grp = '';
        foreach ( $critGroup as $group ) {
          $grp .= (($grp == '') ? '' : '|') . $line [$group];
        }
        $groupRes [$grp] = $line ['cpt'];
      }
    }
    return $groupRes;
  }

// BEGIN - ADD BY TABARY - GET FIRST OBJECT FROM SELECT
  /**
   * ==========================================================================
   * Retrieve the first object from the Database
   * Called from an empty object of the expected class
   *
   * @param string $class
   *          The object class
   * @param array $critArray 
   *                the critera as an array
   * @return object : The object find - If not found set attribut _singleElementNotFound
   */
  public static function getFirstSqlElementFromCriteria($class, $critArray) {
    $obj = new $class ();
    if ($class == 'Attachment') {
      if (array_key_exists ( 'refType', $critArray )) {
        if ($critArray ['refType'] == 'User' or $critArray ['refType'] == 'Contact') {
          $critArray ['refType'] = 'Resource';
        }
      }
    }
    $objList = $obj->getSqlElementsFromCriteria ( $critArray, true );
    if (count ( $objList ) == 0) {
      $obj->_singleElementNotFound = true;
      return $obj;
    } else {
        return $objList [0];
    }
  }
  
// END - ADD BY TABARY - GET FIRST OBJECT FROM SELECT
  
  /**
   * ==========================================================================
   * Retrieve a single object from the Database
   * Called from an empty object of the expected class
   *
   * @param $critArray the
   *          critera asd an array
   * @param $initializeIfEmpty boolean
   *          indicating if no result returns en initialised element or not
   * @return an array of objects
   */
  public static function getSingleSqlElementFromCriteria($class, $critArray, $withoutDepedentElements=false) {
    $obj = new $class ();
    if ($class == 'Attachment') {
      if (array_key_exists ( 'refType', $critArray )) {
        if ($critArray ['refType'] == 'User' or $critArray ['refType'] == 'Contact') {
          $critArray ['refType'] = 'Resource';
        }
      }
    }
    $objList = $obj->getSqlElementsFromCriteria ( $critArray, true,null,null,null,$withoutDepedentElements );
    if (count ( $objList ) == 1) {
      return $objList [0];
    } else {
      $obj->_singleElementNotFound = true;
      if (count ( $objList ) > 1) {
        // traceLog("getSingleSqlElementFromCriteria for object '" . $class . "' returned more than 1 element");
        $obj->_tooManyRows = true;
      }
      return $obj;
    }
  }

  /**
   * ==========================================================================
   * Retrieve an object from the Request (modified Form)
   *
   * @return void (operate directly on the object)
   */
  private function fillSqlElementFromRequest($included = false, $ext = null) {
    foreach ( $this as $key => $value ) {
      // If property is an object, recusively fill it
      if (ucfirst ( $key ) == $key and substr ( $key, 0, 1 ) != "_") {
        if (is_object ( $key )) {
          $subObjectClass = get_class ( $key );
          $subObject = $key;
        } else {
          $subObjectClass = $key;
          $subObject = new $subObjectClass ();
        }
        $subObject->fillSqlElementFromRequest ( true, $ext );
        $this->$key = $subObject;
      } else {
        if (substr ( $key, 0, 1 ) == "_") {
          // not a real field
        } else {
          $dataType = $this->getDataType ( $key );
          $dataLength = $this->getDataLength ( $key );
          $formField = $key . $ext;
          if ($included) { // if included, then object is called recursively, name is prefixed by className
            $formField = get_class ( $this ) . '_' . $key . $ext;
          }
          if ($dataType == 'int') {
            if ($dataLength == 1 and substr ( $key, 0, 11 ) != 'periodicity') {
              if (array_key_exists ( $formField, $_REQUEST )) {
                // if field is hidden, must check value, otherwise just check existence
                // if ($this->isAttributeSetToField($key, 'hidden')) {
                // V5.4 : for action isPrivate can be dynamically hidden, was not detected with prior test
                if ($_REQUEST [$formField] === '0' or $_REQUEST [$formField] === '1') {
                  $this->$key = $_REQUEST [$formField];
                } else if ($_REQUEST [$formField] == '') {
                  $this->$key = 0;
                } else {
                  $this->$key = 1;
                }
              } else {
                // echo "val=False<br/>";
                $this->$key = 0;
              }
            } else if (array_key_exists ( $formField, $_REQUEST )) {
              $this->$key = Security::checkValidInteger ( $_REQUEST [$formField] );
            }
          } else if ($dataType == 'datetime') {
            $formFieldBis = $key . "Bis" . $ext;
            if ($included) {
              $formFieldBis = get_class ( $this ) . '_' . $key . "Bis" . $ext;
            }
            if (isset ( $_REQUEST [$formFieldBis] )) {
              $test = Security::checkValidDateTime ( $_REQUEST [$formField] );
              $test = Security::checkValidDateTime ( $_REQUEST [$formFieldBis] );
              $this->$key = $_REQUEST [$formField] . " ";
              if (substr ( $_REQUEST [$formFieldBis], 0, 1 ) == 'T') {
                $this->$key .= substr ( $_REQUEST [$formFieldBis], 1 );
              } else {
                $this->$key .= $_REQUEST [$formFieldBis];
              }
            } else {
              // hidden field
              if (isset ( $_REQUEST [$formField] )) {
                $this->$key = $_REQUEST [$formField];
              }
            }
          } else if ($dataType == 'decimal' and (substr ( $key, - 4, 4 ) == 'Work')) {
            if (array_key_exists ( $formField, $_REQUEST )) {
              if (get_class ( $this ) == 'WorkElement') {
                $this->$key = Work::convertImputation ( $_REQUEST [$formField] );
              } else {
                $this->$key = Work::convertWork ( $_REQUEST [$formField] );
              }
            }
          } else if ($dataType == 'time') {
            if (array_key_exists ( $formField, $_REQUEST )) {
              $test = Security::checkValidDateTime ( $_REQUEST [$formField] );
              $this->$key = substr ( $_REQUEST [$formField], 1 );
            }
          } else if ($dataType == 'date') {
            if (array_key_exists ( $formField, $_REQUEST )) {
              $test = Security::checkValidDateTime ( $_REQUEST [$formField] );
              $this->$key = $_REQUEST [$formField];
            }
          } else if ($dataType == 'varchar' or substr ( $dataType, - 4 ) == 'text') {
            if (array_key_exists ( $formField, $_REQUEST )) {
              $this->$key=preg_replace('/[\xF0-\xF7].../s', '', $_REQUEST [$formField]);
            }
          } else {
            if (array_key_exists ( $formField, $_REQUEST )) {
              $this->$key = $_REQUEST [$formField];
            }
          }
          // FOR PHP 7.1 COMPATIBILITY
          if (($this->$key===null or $this->$key==='') 
          and ( ($dataType=='decimal') or ($dataType=='numeric') ) 
          and $key!='warningValue' and $key!='alertValue'
//ELIOTT - LEAVE SYSTEM
          and !(in_array(get_class($this),self::$_classesArrayToBypassPHPCompatibilityIf)  and in_array($key,self::$_attributesArrayToBypassPHPCompatibilityIf)) ) {
//ELIOTT - LEAVE SYSTEM
            $this->$key=0;
          }
        }
      }
    }
    if (RequestHandler::isCodeSet('moveToAfterCreate')) {
      $this->_moveToAfterCreate=RequestHandler::getId('moveToAfterCreate');
    }
  }

  /**
   * ==========================================================================
   * Retrieve an object from the Database
   *
   * @return void
   */
  private function getSqlElement($withoutDependentObjects = false) {
    global $globalSilentErrors;
    $curId = $this->id;
    if (! trim ( $curId )) {
      $curId = null;
    }
    
    // Cache management
    if ($curId and array_key_exists ( get_class ( $this ), self::$_cachedQuery )) {
      $whereClause = '#id=' . $curId;
      $class = get_class ( $this );
      if (array_key_exists ( $whereClause, self::$_cachedQuery [$class] )) {
        $obj = self::$_cachedQuery [$class] [$whereClause];
        foreach ( $obj as $fld => $val ) {
          $this->$fld = $obj->$fld;
        }
        return;
      }
    }
    $empty = true;
    // If id is set, get the element from Database
    if ($curId != NULL) {
      $query = "select * from " . $this->getDatabaseTableName () . ' where id=' . $curId;
      foreach ( $this->getDatabaseCriteria () as $critFld => $critVal ) {
        $query .= ' and ' . $critFld . ' = ' . Sql::str ( $critVal );
      }
      $result = Sql::query ( $query );
      if (Sql::$lastQueryNbRows > 0) {
        $empty = false;
        $line = Sql::fetchLine ( $result );
        if (! is_array($line)) {
          errorLog("Error in getSqlElement() for ".get_class($this)." #$this->id : no data retreived. Exiting script");
          exit;
        }
        // get all data fetched
        foreach ( $this as $col_name => $col_value ) {
          if (substr ( $col_name, 0, 1 ) == "_") {
            $colName = substr ( $col_name, 1 );
            if (is_array ( $this->{$col_name} ) and ucfirst ( $colName ) == $colName and ! $withoutDependentObjects) {
              if (substr ( $colName, 0, 4 ) == "Link") {
                $linkClass = null;
                if (strlen ( $colName ) > 4) {
                  $linkClass = substr ( $colName, 5 );
                }
                $this->{$col_name} = Link::getLinksForObject ( $this, $linkClass );
              } else if ($colName == "ResourceCost") {
                $this->{$col_name} = $this->getResourceCost ();
              } else if ($colName == "VersionProject") {
                if (get_class ( $this ) != 'OriginalVersion' and get_class ( $this ) != 'TargetVersion' and get_class ( $this ) != 'OriginalProductVersion' and get_class ( $this ) != 'TargetProductVersion' and get_class ( $this ) != 'OriginalComponentVersion' and get_class ( $this ) != 'TargetComponentVersion') {
                  $vp = new VersionProject ();
                  $idCrit = 'id' . ((get_class ( $this ) == 'Project') ? 'Project' : 'Version');
                  $crit = array($idCrit => $this->id);
                  $this->{$col_name} = $vp->getSqlElementsFromCriteria ( $crit, false );
                }
              } else if ($colName == "DocumentVersion") {
                $dv = new DocumentVersion ();
                $crit = array('idDocument' => $this->id);
                $this->{$col_name} = $dv->getSqlElementsFromCriteria ( $crit, false );
              } else if ($colName == "ExpenseDetail") {
                $this->{$col_name} = $this->getExpenseDetail ();
              } else if (substr ( $colName, 0, 10 ) == "Dependency") {
                $depType = null;
                $crit = Array();
                if (strlen ( $colName ) > 10) {
                  $depType = substr ( $colName, 11 );
                  if ($depType == "Successor") {
                    $crit = Array("PredecessorRefType" => get_class ( $this ), "PredecessorRefId" => $this->id);
                  } else {
                    $crit = Array("SuccessorRefType" => get_class ( $this ), "SuccessorRefId" => $this->id);
                  }
                }
                $dep = new Dependency ();
                $this->{$col_name} = $dep->getSqlElementsFromCriteria ( $crit, false );
              } else {
                if (! $withoutDependentObjects and substr(get_class($this),-4)!='Main') {
                  $this->{$col_name} = $this->getDependantSqlElements ( $colName );
                }
              }
            }
          } else if (ucfirst ( $col_name ) == $col_name) {
            if (! $withoutDependentObjects ) {
              $this->{$col_name} = $this->getDependantSqlElement ( $col_name );
            }
          } else if (strpos ( $this->getFieldAttributes ( $col_name ), 'calculated' ) !== false) {} else {
            // $test=$line[$this->getDatabaseColumnName($col_name)];
            $dbColName = $this->getDatabaseColumnName ( $col_name );
            $dbType=$this->getDataType($col_name);
            $dbLength=$this->getDataLength($col_name);
            if (array_key_exists ( $dbColName, $line )) {
              $this->{$col_name} = $line [$dbColName];
            } else if (array_key_exists ( strtolower ( $dbColName ), $line )) {
              $dbColName = strtolower ( $dbColName );
              $this->{$col_name} = $line [$dbColName];
            } else {
              if (! $globalSilentErrors)
                errorLog ( "Error on SqlElement to get '" . $col_name . "' for Class '" . get_class ( $this ) . "' " . " : field '" . $dbColName . "' not found in Database on table ".$this->getDatabaseTableName() );
            }
            // FOR PHP 7.1 COMPATIBILITY
            //if ($this->{$col_name}===null    // FOR TEST PURPOSE
            if ($this->{$col_name}===''  
            and ( ( $dbType=='numeric') or ( $dbType=='decimal') ) 
            and $col_name!='warningValue' and $col_name!='alertValue'
//ELIOTT - LEAVE SYSTEM
          and !(in_array(get_class($this),self::$_classesArrayToBypassPHPCompatibilityIf)  and in_array($col_name,self::$_attributesArrayToBypassPHPCompatibilityIf)) ) {
//ELIOTT - LEAVE SYSTEM
              $this->{$col_name}=0;
            }
          }
        }
      } else {
        $this->id = null;
      }
    }
    if ($empty and ! $withoutDependentObjects) {
      // Get all the elements that are objects (first letter is uppercase in object properties)
      foreach ( $this as $key => $value ) {
        // echo substr($key,0,1) . "<br/>";
        if (ucfirst ( $key ) == $key and substr ( $key, 0, 1 ) != "_") {
          $this->{$key} = $this->getDependantSqlElement ( $key );
        }
      }
    }
    // set default idUser if exists
    if ($empty and property_exists ( $this, 'idUser' ) and get_class ( $this ) != 'Affectation' and get_class ( $this ) != 'Message') {
      if (sessionUserExists ()) {
        $this->idUser = getSessionUser ()->id;
      }
    }
    if ($curId and array_key_exists ( get_class ( $this ), self::$_cachedQuery )) {
      $whereClause = '#id=' . $curId;
      $class = get_class ( $this );
      self::$_cachedQuery [get_class ( $this )] [$whereClause] = clone ($this);
    }
  }

  /**
   * ==========================================================================
   * retrieve single object included in an object from the Database
   *
   * @param $objClass the
   *          name of the class of the included object
   * @return an object
   */
  private function getDependantSqlElement($objClass) {
    $curId = $this->id;
    if (! trim ( $curId )) {
      $curId = null;
    }
    $obj = new $objClass ( null, true );
    $obj->refId = $this->id;
    $obj->refType = get_class ( $this );
    if (substr($obj->refType,-4)=='Main') $obj->refType=substr($obj->refType,0,-4);
    // If id is set, get the elements from Database
    if (($curId != null) and ($obj instanceof SqlElement)) {
      $obj->getSqlElement ( true );
      // set the reference data
      // build query
      $query = "select id from " . $obj->getDatabaseTableName () . ' where refId =' . $curId . " and refType ='" . $obj->refType . "'";
      foreach ( $obj->getDatabaseCriteria () as $critFld => $critVal ) {
        $query .= ' and ' . $critFld . ' = ' . Sql::str ( $critVal );
      }
      $result = Sql::query ( $query );
      // if no element in database, will return empty object
      //
      // IMPROVEMENT ON V4.2.0 : attention, this may return results when it did not previously...
      if (Sql::$lastQueryNbRows > 0) {
        $line = Sql::fetchLine ( $result );
        // get all data fetched for the dependant element
        $obj->id = $line ['id'];
        $obj->getSqlElement ( true );
      }
    }
    // set the dependant element
    return $obj;
  }

  /**
   * ==========================================================================
   * retrieve objects included in an object from the Database
   *
   * @param $objClass the
   *          name of the class of the included object
   * @return an array ob objects
   */
  private function getDependantSqlElements($objClass) {
    if (get_class($this)=='Organization' and $objClass=='Resource') $objClass='ResourceAll';
    $curId = $this->id;
    $obj = new $objClass ();
    $list = array();
    // $obj->refId=$this->id;
    // $obj->refType=get_class($this);
    // If id is set, get the elements from Database
    if (($curId != NULL) and ($obj instanceof SqlElement)) {
      // set the reference data
      // build query
      $query = "select id from " . $obj->getDatabaseTableName ();
      if (property_exists ( $objClass, 'id' . get_class ( $this ) ) and $objClass != 'Note' and $objClass != 'Attachment' and $objClass != 'Link') {
        $query .= " where " . $obj->getDatabaseColumnName ( 'id' . get_class ( $this ) ) . "= " . Sql::str ( $curId ) . " ";
      } else {
        $refType = get_class ( $this );
        if ($refType == 'TicketSimple') {
          $refType = 'Ticket';
        }
        $query .= " where refId =" . Sql::str ( $curId ) . " " . " and refType ='" . $refType . "'";
      }
      $query .= " order by id desc ";
      $result = Sql::query ( $query );
      // if no element in database, will return empty array
      if (Sql::$lastQueryNbRows > 0) {
        while ( $line = Sql::fetchLine ( $result ) ) {
          $newObj = new $objClass ();
          $newObj->id = $line ['id'];
          $newObj->getSqlElement ();
          $list [] = $newObj;
        }
      }
    }
    // set the dependant element
    return $list;
  }
  
  // ============================================================================**********
  // GET STATIC DATA FUNCTIONS
  // ============================================================================**********
  
  /**
   * ========================================================================
   * return the type of a column depending on its name
   *
   * @param $colName the
   *          name of the column
   * @return the type of the data
   */
  public function getDataType($colName) {
    $colName = strtolower ( $colName );
    $formatList = self::getFormatList ( get_class ( $this ) );
    if (! array_key_exists ( $colName, $formatList )) {
      foreach ( $this as $col => $val ) {
        if (is_object ( $val )) {
          $subObj = new $col ();
          $subType = $subObj->getDataType ( $colName );
          if ($subType != 'undefined') {
            return $subType;
          }
        }
      }
      return 'undefined';
    }
    $fmt = $formatList [$colName];
    $split = preg_split ( '/[()\s]+/', $fmt, 2 );
    return $split [0];
  }

  /**
   * ========================================================================
   * return the length (max) of a column depending on its name
   *
   * @param $colName the
   *          name of the column
   * @return the type of the data
   */
  public function getDataLength($colName) {
    $colName = strtolower ( $colName );
    $formatList = self::getFormatList ( get_class ( $this ) );
    if (! array_key_exists ( $colName, $formatList )) {
      return 0;
    }
    $fmt = $formatList [$colName];
    $split = preg_split ( '/[()\s]+/', $fmt, 3 );
    $type = $split [0];
    if ($type == 'date') {
      return 10;
    } else if ($type == 'time') {
      return 5;
    } else if ($type == 'timestamp' or $type == 'datetime') {
      return 19;
    } else if ($type == 'double') {
      return 2;
    } else if ($type == 'text') {
      return 65535;
    } else if ($type == 'mediumtext') {
      return 16777215;
    } else if ($type == 'longtext') {
      return 4294967295;
    } else {
      if (count ( $split ) >= 2) {
        return ($split [1]);
      } else {
        return 0;
      }
    }
  }

  /**
   * ========================================================================
   * return the generic layout for grit list
   *
   * @return the layout from static data
   */
  public function getLayout() {
    $result = "";
    $columns = ColumnSelector::getColumnsList ( get_class ( $this ) );
    $totWidth = 0;
    foreach ( $columns as $col ) {
      if ($col->hidden) {
        continue;
      }
      if (! self::isVisibleField ( $col->attribute )) {
        continue;
      }
      $result .= '<th';
      $result .= ' field="' . htmlEncode ( $col->field ) . '"';
      $result .= ' width="' . (($col->field == 'name') ? 'auto' : $col->widthPct . '%') . '"';
      $result .= ($col->formatter) ? ' formatter="' . htmlEncode ( $col->formatter ) . '"' : '';
      $result .= ($col->_from) ? ' from="' . $col->_from . '"' : '';
      $result .= ($col->hidden and $col->field!='id') ? ' hidden="true"' : '';
      $result .= '>' . $col->_displayName . '</th>' . "\n";
      $totWidth += ($col->field == 'name') ? 0 : $col->widthPct;
    }
    if ($totWidth < 90) {
      $autoWidth = 100 - $totWidth;
    } else {
      $autoWidth = 10;
    }
    $result = str_replace ( 'width="auto"', 'width="' . $autoWidth . '%"', $result );
    return $result;
  }
  
  // ADD BY Marc TABARY - 2017-03-02 - DRAW SPINNER
  /**
   * ========================================================================
   * return the spinner attributes (min, max, step) for a given field
   *
   * @return string
   */
  public function getSpinnerAttributes($fieldName) {
    $spinnersAttributes = $this->getStaticSpinnersAttributes ();
    if (array_key_exists ( $fieldName, $spinnersAttributes )) {
      return $spinnersAttributes [$fieldName];
    } else {
      return '';
    }
  }
  // END ADD BY Marc TABARY - 2017-03-02 - DRAW SPINNER
  
  // ADD BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
  /**
   * ========================================================================
   * return the array of fields to disabled on form change
   *
   * @return '' or a string structured like js array
   */
  public function getDisabledFieldsOnChange() {
    $disabledFieldsOnChange = $this->getStaticDisabledFieldsOnChange ();
    $theString = '';
    // The main object
    if ($disabledFieldsOnChange !== null and is_array ( $disabledFieldsOnChange ) and count ( $disabledFieldsOnChange ) > 0) {
      $theClass = get_class ( $this );
      foreach ( $disabledFieldsOnChange as $disabledFieldOnChange ) {
        $theString .= '"' . $theClass . $disabledFieldOnChange . '",';
      }
    }
    // The sub-objects
    foreach ( $this as $field => $val ) {
      if (is_object ( $val ) and substr ( $field, 0, 1 ) != '_') {
        $disabledFieldsOnChange = $val->getStaticDisabledFieldsOnChange ();
        if ($disabledFieldsOnChange !== null and is_array ( $disabledFieldsOnChange ) and count ( $disabledFieldsOnChange ) > 0) {
          $theClass = get_class ( $val );
          foreach ( $disabledFieldsOnChange as $disabledFieldOnChange ) {
            // Special for '_spe_' : Because the id Dom is construct by hand, it's simplier like this
            if (substr ( $disabledFieldOnChange, 0, 5 ) === '_spe_') {
              $theString .= '"_' . $disabledFieldOnChange . '",';
            } else {
              $theString .= '"' . $theClass . '_' . $disabledFieldOnChange . '",';
            }
          }
        }
      }
    }
    
    if ($theString != '') {
      $theString = '[' . substr ( $theString, 0, - 1 ) . ']';
    }
    return $theString;
  }
  // END ADD BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
  
// BEGIN - ADD BY TABARY - TOOLTIP  
  /**
   * ========================================================================
   * return the tooltip of a given field.
   * @return array of fields with tooltip
   */
  public function getFieldTooltip($fieldName) {
    $fieldsTooltip = $this->getStaticFieldsTooltip();
    if (array_key_exists ( $fieldName, $fieldsTooltip )) {
      return $fieldsTooltip[$fieldName];
    } else {
      return '';
    }
  }
// END - ADD BY TABARY - TOOLTIP  

  
  /**
   * ========================================================================
   * return the generic attributes (required, disabled, .
   *
   * ..) for a given field
   *
   * @return an array of fields with specific attributes
   */
  public function getFieldAttributes($fieldName) {
    $fieldsAttributes = $this->getStaticFieldsAttributes ();
    if (array_key_exists ( $fieldName, $fieldsAttributes )) {
      return $fieldsAttributes [$fieldName];
    } else {
      return '';
    }
  }

  public function isAttributeSetToField($fieldName, $attribute) {
    if (strpos ( $this->getFieldAttributes ( $fieldName ), $attribute ) !== false) {
      return true;
    } else {
      return false;
    }
  }

  public function getDisplayStyling($fieldName) {
    $displayStyling=$this->getStaticDisplayStyling();
    if (isset($displayStyling[$fieldName])) {
      $res=$displayStyling[$fieldName];
      if (!isset($res['caption'])) $res['caption']='';
      if (!isset($res['caption'])) $res['caption']='';
      return $res;
    } else {
      return array('caption'=>'','field'=>'');
    }
    
  }
  public function getStaticDisplayStyling() {
    return array();
  }
  /**
   * ========================================================================
   * Return the default value for a given field
   *
   * @return string the name of the data table
   */
  public function getDefaultValue($fieldName, $onSave = false) {
    $defaultValues = $this->getStaticDefaultValues ();
    if (array_key_exists ( $fieldName, $defaultValues )) {
      if (substr ( $defaultValues [$fieldName], 0, strlen ( self::$_evaluationString ) ) == self::$_evaluationString) {
        $eval = substr ( $defaultValues [$fieldName], strlen ( self::$_evaluationString ) );
        // $eval='$value='. str_replace("'",'"',$eval).";";
        $eval = '$value=' . str_replace ( '\$', '$', $eval ) . ';';
        eval ( $eval );
        return $value;
      } else if (! $onSave) {
        return $defaultValues [$fieldName];
      }
    } else {
      return null;
    }
  }

  public function getDefaultValueString($fieldName) {
    $defaultValues = $this->getStaticDefaultValues ();
    if (array_key_exists ( $fieldName, $defaultValues )) {
      return $defaultValues [$fieldName];
    } else {
      return null;
    }
  }

  public function checkValidEvaluationString($str) {
    if (substr ( $str, 0, strlen ( self::$_evaluationString ) ) != self::$_evaluationString)
      return false;
      // Some check to avoid hack (even if this feature should only be given to admin)
    foreach ( self::$_evaluationStringForbiddenKeywords as $keyword ) {
      if (strpos ( $str, $keyword ) !== false)
        return false;
    }
    $eval = substr ( $str, strlen ( self::$_evaluationString ) );
    $eval = '$value=' . $eval . ';$resultOkForCheckValidEvaluationString=true;';
    disableCatchErrors ();
    @eval ( $eval );
    enableCatchErrors ();
    if (isset ( $resultOkForCheckValidEvaluationString ) and $resultOkForCheckValidEvaluationString == true)
      return true;
    return false;
  }

  /**
   * ========================================================================
   * Return the default value for a given field
   *
   * @return string the name of the data table
   */
  public function setAllDefaultValues($onSave = false) {
    $defaultValues = $this->getStaticDefaultValues ();
    foreach ( $defaultValues as $field => $value ) {
      if ($onSave) {
        if ($this->isAttributeSetToField ( $field, 'readonly' )) {
          $def = $this->getDefaultValue ( $field, $onSave );
          if ($def !== null)
            $this->$field = $def;
        }
      } else {
        if (! $this->id) {
          $this->$field = $this->getDefaultValue ( $field );
        } else if ($this->$field === null and $this->isAttributeSetToField ( $field, 'required' )) {
          $this->$field = $this->getDefaultValue ( $field );
        }
      }
    }
  }

  /**
   * ========================================================================
   * Return the name of the table in the database
   * Default is the name of the class (lowercase)
   * May be overloaded for some classes, who reference a table different
   * from class name
   *
   * @return string the name of the data table
   */
  public function getDatabaseTableName() {
    if (substr(get_class($this),-4)=='Main') {
      $notMain=substr(get_class($this),0,-4);
      $obj=new $notMain();
      return $obj->getDatabaseTableName();
    } else {
      return $this->getStaticDatabaseTableName ();
    }
  }

  /**
   * ========================================================================
   * Return the name of the column name in the table in the database
   * Default is the name of the field
   * May be overloaded for some fields of some classes
   * @param string $field : The field to find as data column in database
   * @param boolean $emptyIfNotFound : If True = Returns empty string if not found
   * @return string the name of the data column
   */
  public function getDatabaseColumnName($field,$emptyIfNotFound=false) {
    $colName = $field;
    $databaseColumnName = $this->getStaticDatabaseColumnName ();
    if (array_key_exists ( $field, $databaseColumnName )) {
      $colName = $databaseColumnName [$field];
    } else if($emptyIfNotFound) {
        return "";
    } 
    // else {
      // return Sql::str($field); // Must not be quoted : would return 'name' (with quotes)
      // return $field;
      // }
      // if (Sql::isPgsql() ) {
      // $colName=strtolower($colName);
      // }
    return $colName;
  }
  public function replaceDatabaseColumnNameInWhereClause($where) {
    foreach ($this->getStaticDatabaseColumnName() as $from=>$to) {
      $where=str_replace($from,$to,$where);
    }
    return $where;
  }

  /**
   * ========================================================================
   * Return the name of the field in the object from the column name in the
   * table in the database
   * (it is the reversed method from getDatabaseColumnName()
   * Default is the name of the field
   * May be overloaded for some fields of some classes
   *
   * @return string the name of the data column
   */
  public function getDatabaseColumnNameReversed($field) {
    $databaseColumnName = $this->getStaticDatabaseColumnName ();
    $databaseColumnNameReversed = array_flip ( array_map ( 'strtolower', $databaseColumnName ) );
    $field = strtolower ( $field );
    if (array_key_exists ( strtolower ( $field ), $databaseColumnNameReversed )) {
      return $databaseColumnNameReversed [$field];
    } else {
      return $field;
    }
  }

  /**
   * ========================================================================
   * Return the additional criteria to select class elements in the database
   * Default is empty string
   * May be overloaded for some classes, which reference a table different
   * from class name
   *
   * @return array listing criteria
   */
  public function getDatabaseCriteria() {
    return $this->getStaticDatabaseCriteria ();
  }

  /**
   * ============================================================================
   * Return the caption of a field using i18n translation
   *
   * @param $fld the
   *          name of the field
   * @return the translated colXxxxxx value
   */
  function getColCaption($fld) {
    if (! $fld or $fld == '') {
      return '';
    }
    $colCaptionTransposition = $this->getStaticColCaptionTransposition ( $fld );
    if (array_key_exists ( $fld, $colCaptionTransposition )) {
      $fldName = $colCaptionTransposition [$fld];
    } else {
      $fldName = $fld;
    }
    return i18n ( 'col' . ucfirst ( $fldName ) );
  }

  public function getLowercaseFieldsArray($limitToExportableFields = false) {
    $arrayFields = array();
    if (method_exists($this, 'setAttributes')) $this->setAttributes();
    $extraHiddenFields = $this->getExtraHiddenFields(null, null, getSessionUser()->getProfile(), $limitToExportableFields);
    foreach ( $this as $fld => $fldVal ) {
      if (is_object ( $this->$fld )) {
        $arrayFields = array_merge ( $arrayFields, $this->$fld->getLowercaseFieldsArray ( $limitToExportableFields ) );
      } else {
        if ($limitToExportableFields) {
          if (($this->isAttributeSetToField($fld, 'hidden') or $this->isAttributeSetToField($fld, 'hiddenforce') or in_array($fld, $extraHiddenFields)) and ! $this->isAttributeSetToField($fld, 'forceExport')) continue;
        }
        $arrayFields [strtolower ( $fld )] = $fld;
      }
    }
    return $arrayFields;
  }

  public function getFieldsArray($limitToExportableFields = false) {
    $arrayFields = array();
    if (method_exists($this, 'setAttributes')) $this->setAttributes();
    $extraHiddenFields = $this->getExtraHiddenFields ( null, null, getSessionUser ()->getProfile (), $limitToExportableFields );
    foreach ( $this as $fld => $fldVal ) {
      if (is_object ( $this->$fld )) {
        $arrayFields = array_merge ( $arrayFields, $this->$fld->getFieldsArray ( $limitToExportableFields ) );
      } else {
        if ($limitToExportableFields) {
          // CHANGE BY Marc TABARY - 2017-03-20 - FORCE HIDDEN OR READONLY
          if (($this->isAttributeSetToField ( $fld, 'hidden' ) or $this->isAttributeSetToField ( $fld, 'hiddenforce' ) or in_array ( $fld, $extraHiddenFields )) and ! $this->isAttributeSetToField ( $fld, 'forceExport' )) {
            // Old
            // if ($this->isAttributeSetToField($fld,'hidden') and ! $this->isAttributeSetToField($fld,'forceExport')) {
            // END CHANGE BY Marc TABARY - 2017-03-20 - FORCE HIDDEN OR READONLY
            continue;
          }
        }
        $arrayFields [$fld] = $fld;
      }
    }
    return $arrayFields;
  }

  /**
   * =========================================================================
   * Return the list of fields format and store it in static array of formats
   * to be able to fetch it again without requesting it from database
   *
   * @param $class the
   *          class of the object
   * @return the format list
   */
  private static function getFormatList($class) {
    if (count ( self::$_tablesFormatList ) == 0) { // if static value not initalized, try and retrieve from session
      $fromSession = getSessionValue ( '_tablesFormatList' );
      if ($fromSession == null) {
        setSessionValue ( '_tablesFormatList', self::$_tablesFormatList );
      } else {
        self::$_tablesFormatList = $fromSession;
      }
    }
    if (array_key_exists ( $class, self::$_tablesFormatList )) {
      return self::$_tablesFormatList [$class];
    }
    $obj = new $class ();
    $formatList = array();
    $query = "desc " . $obj->getDatabaseTableName();
    if (Sql::isPgsql ()) {
      $query = "SELECT a.attname as field, pg_catalog.format_type(a.atttypid, a.atttypmod) as type" . " FROM pg_catalog.pg_attribute a " . " WHERE a.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname='" . $obj->getDatabaseTableName () . "')" . " AND a.attnum > 0 AND NOT a.attisdropped" . " ORDER BY a.attnum";
    }
    $result = Sql::query ( $query );
    while ( $line = Sql::fetchLine ( $result ) ) {
      $dbFieldName = (isset ( $line ['Field'] )) ? $line ['Field'] : $line ['field'];
      $fieldName = $obj->getDatabaseColumnNameReversed ( $dbFieldName );
      $type = (isset ( $line ['Type'] )) ? $line ['Type'] : $line ['type'];
      $from = array();
      $to = array();
      if (Sql::isPgsql ()) {
        $from [] = 'integer';
        $to [] = 'int(12)';
        $from [] = 'numeric(12,0)';
        $to [] = 'int(12)';
        $from [] = 'numeric(5,0)';
        $to [] = 'int(5)';
        $from [] = 'numeric(3,0)';
        $to [] = 'int(3)';
        $from [] = 'numeric(1,0)';
        $to [] = 'int(1)';
        $from [] = ' without time zone';
        $to [] = '';
        $from [] = 'character varying';
        $to [] = 'varchar';
        $from [] = 'numeric';
        $to [] = 'decimal';
        $from [] = 'timestamp';
        $to [] = 'datetime';
      }
      $from [] = 'mediumtext';
      $to [] = 'varchar(16777215)';
      $from [] = 'longtext';
      $to [] = 'varchar(4294967295)';
      $from [] = 'text';
      $to [] = 'varchar(65535)';
      $from [] = 'bigint';
      $to [] = 'int';
      $from [] = 'int unsigned';
      $to [] = 'int';
            
      $type = str_ireplace ( $from, $to, $type );
      if (substr($type,0,3)=='int') $type=trim(str_replace('unsigned','',$type));
      if ($fieldName=='id') {
        $type='int(12)';
      } else if ($type=='int' or $type=='int(10)' or $type=='int(11)') {
        $tableName=$obj->getDatabaseTableName();
        $dbName=Parameter::getGlobalParameter('paramDbName');
        $sqlColumn="SELECT COLUMN_COMMENT as comment FROM INFORMATION_SCHEMA.COLUMNS "
           ." WHERE TABLE_SCHEMA='$dbName' AND TABLE_NAME='$tableName' AND COLUMN_NAME='$dbFieldName'";
        $resultColumn=Sql::query($sqlColumn);
        $line=Sql::fetchLine($resultColumn);
        if ($line and isset($line['comment'])) $type='int('.$line['comment'].')';
      }
      $formatList [strtolower($fieldName)]=$type;
      if (strtolower($fieldName)=='idplanningmode') {
        $pmFld='id'.str_replace('PlanningElement', '', $class).'PlanningMode';
        $formatList [strtolower ( $pmFld )] = $type;
      } else if ($fieldName=='id'.str_replace('PlanningElement', '', $class).'PlanningMode') {
        $formatList ['idplanningmode'] = $type;
      }
    }
    self::$_tablesFormatList [$class] = $formatList;
    setSessionValue ( '_tablesFormatList', self::$_tablesFormatList ); // store session value (as initalized)
    return $formatList;
  }

  /**
   * ========================================================================
   * return the generic layout
   *
   * @return the layout from static data
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }
  
  // ADD BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
  /**
   * ==========================================================================
   * Return the generic disabledFieldOnChange
   *
   * @return array[name] : the generic $_disabledFieldOnChange
   */
  protected function getStaticDisabledFieldsOnChange() {
    return self::$_disabledFieldsOnChange;
  }
  // END ADD BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
  
  // ADD BY Marc TABARY - 2017-03-02 - DRAW SPINNER
  /**
   * ==========================================================================
   * Return the generic spinnerAttributes
   *
   * @return array[name,value] : the generic $_spinnerAttributes
   */
  protected function getStaticSpinnersAttributes() {
    return self::$_spinnersAttributes;
  }
  // END ADD BY Marc TABARY - 2017-03-02 - DRAW SPINNER
  
// BEGIN - ADD BY TABARY - TOOLTIP    
  /**
   * ==========================================================================
   * Return the generic fieldsTooltip
   *
   * @return array the fieldsTooltip array
   */
  protected function getStaticFieldsTooltip() {
    return self::$_fieldsTooltip;
  }
// END - ADD BY TABARY - TOOLTIP    

  
  /**
   * ==========================================================================
   * Return the generic fieldsAttributes
   *
   * @return the layout
   */
  protected function getStaticFieldsAttributes() {
    return self::$_fieldsAttributes;
  }

  /**
   * ==========================================================================
   * Return the generic defaultValues
   *
   * @return the layout
   */
  protected function getStaticDefaultValues() {
    return self::$_defaultValues;
  }

  /**
   * ==========================================================================
   * Return the generic databaseTableName
   *
   * @return the layout
   */
  protected function getStaticDatabaseTableName() {
    $paramDbPrefix = Parameter::getGlobalParameter ( 'paramDbPrefix' );
    return strtolower ( $paramDbPrefix . get_class ( $this ) );
  }

  /**
   * ========================================================================
   * Return the generic databaseTableName
   *
   * @return the databaseTableName
   */
  protected function getStaticDatabaseColumnName() {
    return array();
  }

  /**
   * ========================================================================
   * Return the generic database criteria
   *
   * @return the databaseTableName
   */
  protected function getStaticDatabaseCriteria() {
    return array();
  }

  /**
   * ============================================================================
   * Return the specific colCaptionTransposition
   *
   * @return the colCaptionTransposition
   */
  protected function getStaticColCaptionTransposition($fld = null) {
    return array();
  }
  
  // ============================================================================**********
  // GET VALIDATION SCRIPT
  // ============================================================================**********
  
  /**
   * ========================================================================
   * return generic javascript to be executed on validation of field
   *
   * @param $colName the
   *          name of the column
   * @return the javascript code
   */
  public function getValidationScript($colName) {
    $colScript = '';
    $posDate = strlen ( $colName ) - 4;
    
    // ADD BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
    $specificWidgetsToDisabled = $this->getDisabledFieldsOnChange ();
    // END ADD BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
    
    if (substr ( $colName, 0, 2 ) == 'id' and strlen ( $colName ) > 2) { // SELECT => onChange
      $colScript .= '<script type="dojo/connect" event="onChange" args="evt">';
      $colScript .= '  if (this.value!=null && this.value!="") { ';
      // CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '    formChanged(' . $specificWidgetsToDisabled . ');';
      // Old
      // $colScript .= ' formChanged();';
      // END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '  }';
      // if ( get_class($this)=='Activity' or get_class($this)=='Ticket' or get_class($this)=='Milestone' ) {
      if (get_class ( $this ) != 'Project' and get_class ( $this ) != 'Affectation') {
        if ($colName == 'idProject' and property_exists ( $this, 'idActivity' )) {
          $colScript .= '   refreshList("idActivity","idProject", this.value);';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idResource' )) {
          $required = 'false';
          if ($this->isAttributeSetToField ( 'idResource', 'required' )) $required = 'true';
          $colScript .= '   refreshList("idResource","idProject", this.value, "' . htmlEncode ( $this->idResource ) . '",null,' . $required . ',null,null,"' . get_class ( $this ) . '");';
          foreach($this as $tmpCol=>$tmpVal) {
            if (substr($tmpCol,-12)=='__idResource') {
              $tmpReq='false';
              if ($this->isAttributeSetToField ( $tmpCol, 'required' )) $tmpReq = 'true';
              $colScript .= '   refreshList("'.$tmpCol.'","idProject", this.value, "' . htmlEncode ( $tmpVal ) . '",null,' . $tmpReq . ',null,null,"' . get_class ( $this ) . '");';
            }
          }
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idAccountable' )) {
          $required = 'false';
          if ($this->isAttributeSetToField ( 'idAccountable', 'required' )) $required = 'true';
          $colScript .= '   refreshList("idAccountable","idProject", this.value, "' . htmlEncode ( $this->idAccountable ) . '",null,' . $required . ',null,null,"' . get_class ( $this ) . '");';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idAffectable' )) {
          $required = 'false';
          if ($this->isAttributeSetToField ( 'idAffectable', 'required' )) $required = 'true';
          $colScript .= '   refreshList("idAffectable","idProject", this.value, "' . htmlEncode ( $this->idAffectable ) . '",null,' . $required . ',null,null,"' . get_class ( $this ) . '");';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idProduct' )) {
          $colScript .= '   refreshList("idProduct","idProject", this.value, dijit.byId("idProduct").get("value"));';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idComponent' )) {
          $colScript .= '   if (dijit.byId("idProduct") && trim(dijit.byId("idProduct").get("value"))) {';
          // $colScript .= ' refreshList("idComponent","idProduct", dijit.byId("idProduct").get("value"), dijit.byId("idComponent").get("value"));';
          $colScript .= '   } else {';
          $colScript .= '     refreshList("idComponent","idProject", this.value, dijit.byId("idComponent").get("value"));';
          $colScript .= '   }';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idProductOrComponent' )) {
          $colScript .= '   refreshList("idProductOrComponent","idProject", this.value, dijit.byId("idProductOrComponent").get("value"));';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'id' . get_class ( $this ) . 'Type' )) {
          $colScript .= '   refreshList("id' . get_class ( $this ) . 'Type","idProject", this.value, dijit.byId("id' . get_class ( $this ) . 'Type").get("value"),null,true);';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idMilestone' )) {
          $colScript .= '   refreshList("idMilestone","idProject", this.value);';
        }
        $arrVers = array(
            'idVersion', 
            'idProductVersion', 
            'idOriginalVersion', 
            'idOriginalProductVersion', 
            'idOriginalComponentVersion', 
            'idTargetVersion', 
            'idTargetProductVersion', 
            'idTargetComponentVersion', 
            'idTestCase', 
            'idRequirement');
        $arrVersProd = array(
            'idVersion', 
            'idProductVersion', 
            'idOriginalVersion', 
            'idOriginalProductVersion', 
            'idTargetVersion', 
            'idTargetProductVersion', 
            'idTestCase', 
            'idRequirement');
        $arrVersComp = array('idVersion', 'idComponentVersion', 'idOriginalComponentVersion', 'idTargetComponentVersion');
        $versionExists = false;
        foreach ( $arrVers as $vers ) {
          if (property_exists ( $this, $vers )) {
            $versionExists = true;
          }
        }
        if ($colName == 'idProject' and $versionExists) {
          foreach ( $arrVersComp as $vers ) {
            if (property_exists ( $this, $vers )) {
              $versProd = str_replace ( 'Component', 'Product', $vers );
              $colScript .= "if (dijit.byId('$versProd') && trim(dijit.byId('$versProd').get('value')) ) {";
              // $colScript.="refreshList('$vers','$versProd', dijit.byId('$versProd').get('value'));";
              $colScript .= " } else if (dijit.byId('idComponent') && trim(dijit.byId('idComponent').get('value'))) {";
              // $colScript.="refreshList('$vers','idComponent', trim(dijit.byId('idComponent').get('value')));";
              $colScript .= " } else {";
              $colScript .= "refreshList('$vers','idProject', this.value);";
              $colScript .= " }";
            }
          }
          foreach ( $arrVersProd as $vers ) {
            if (property_exists ( $this, $vers )) {
              $colScript .= " if (dijit.byId('idProduct') && trim(dijit.byId('idProduct').get('value'))) {";
              // $colScript.="refreshList('$vers','idProduct', trim(dijit.byId('idProduct').get('value')));";
              $colScript .= " } else {";
              $colScript .= "refreshList('$vers','idProject', this.value);";
              $colScript .= " }";
            }
          }
        }
        if ($colName == 'idProduct' and property_exists ( $this, 'idComponent' )) {
          $colScript .= "if (trim(this.value)) {";
          $colScript .= "refreshList('idComponent','idProduct', this.value);";
          $colScript .= "} else {";
          if (property_exists ( $this, 'idProject' )) {
            $colScript .= "refreshList('idComponent','idProject', dijit.byId('idProject').get('value'));";
          }
          $colScript .= "}";
        }
        if ($colName == 'idProduct' and $versionExists) {
          foreach ( $arrVersProd as $vers ) {
            if (property_exists ( $this, $vers )) {
              $colScript .= "if (trim(dijit.byId('idProduct').get('value'))) {";
              $colScript .= "refreshList('$vers','idProduct', this.value);";
              $colScript .= "} else {";
              if (property_exists ( $this, 'idProject' )) {
                $colScript .= "refreshList('$vers','idProject', dijit.byId('idProject').get('value'));";
              }
              $colScript .= "}";
            }
          }
        }
        if ($colName == 'idProduct' and property_exists ( $this, 'idBusinessFeature' )) {
          $colScript .= "refreshList('idBusinessFeature','idProduct', this.value);";
        }
        if ($colName == 'idComponent' and $versionExists) {
          foreach ( $arrVersComp as $vers ) {
            if (property_exists ( $this, $vers )) {
              $versProd = str_replace ( 'Component', 'Product', $vers );
              $colScript .= "if (dijit.byId('$versProd') && trim(dijit.byId('$versProd').get('value'))) {";
              $colScript .= "  if (trim(this.value)) {";
              $colScript .= "  refreshList('$vers','idProductVersion', dijit.byId('$versProd').get('value'), null, null,null,'idComponent',this.value);";
              $colScript .= "  } else {";
              $colScript .= "  refreshList('$vers','idProductVersion', dijit.byId('$versProd').get('value'));";
              $colScript .= "  }";
              $colScript .= "} else if (trim(this.value)) {";
              $colScript .= "refreshList('$vers','idComponent', this.value);";
              $colScript .= "} else {";
              if (property_exists ( $this, 'idProject' )) {
                $colScript .= "refreshList('$vers','idProject', dijit.byId('idProject').get('value'));";
              }
              $colScript .= "}";
            }
          }
        }
        if (substr ( $colName, - 14 ) == 'ProductVersion') {
          $versComp = str_replace ( 'Product', 'Component', $colName );
          if (property_exists ( $this, $versComp )) {
            $colScript .= "if (trim(this.value)) {";
            if (property_exists ( $this, 'idComponent' )) {
              $colScript .= "  if (dijit.byId('idComponent') && trim(dijit.byId('idComponent').get('value')) ) {";
              $colScript .= "refreshList('$versComp','idProductVersion', this.value, null, null, null,'idComponent', dijit.byId('idComponent').get('value'));";
              $colScript .= " } else {";
              $colScript .= "refreshList('$versComp','idProductVersion', this.value);";
              $colScript .= " }";
            }
            if (property_exists ( $this, 'idComponent' )) {
              $colScript .= "} else if (dijit.byId('idComponent') && trim(dijit.byId('idComponent').get('value')) ) {";
              $colScript .= "refreshList('$versComp','idComponent', dijit.byId('idComponent').get('value'));";
            }
            $colScript .= "} else {";
            if (property_exists ( $this, 'idProject' )) {
              $colScript .= "refreshList('$versComp','idProject', dijit.byId('idProject').get('value'));";
            }
            $colScript .= "}";
          }
          $colScript .= 'if (! trim(dijit.byId("idProduct").get("value")) ) {';
          $colScript .= '   setProductValueFromVersion("idProduct",this.value);';
          $colScript .= '}';
        }
        if (substr ( $colName, - 16 ) == 'ComponentVersion') {
          $colScript .= 'if (! trim(dijit.byId("idComponent").get("value")) ) {';
          $colScript .= '   setProductValueFromVersion("idComponent",this.value);';
          $colScript .= '}';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idContact' )) {
          $colScript .= '   refreshList("idContact","idProject", this.value);';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idTicket' )) {
          $colScript .= '   refreshList("idTicket","idProject", this.value);';
        }
        if ($colName == 'idProject' and property_exists ( $this, 'idUser' )) {
          $colScript .= '   refreshList("idUser","idProject", this.value);';
        }
      }
      $typeName='id' . get_class ( $this ) . 'Type';
      if (get_class($this)=='PeriodicMeeting') $typeName='idMeetingType';
      if ($colName == 'idStatus' or $colName == $typeName or substr ( $colName, - 12 ) == 'PlanningMode') {
        $colScript .= '   getExtraRequiredFields();';
      }
      if ($colName == $typeName or $colName == 'idStatus') {
        $colScript .= '   getExtraReadonlyFields("","","");';
// MTY - LEAVE SYSTEM
        if (get_class($this)!="Leave") {
          $colScript .= '   getExtraHiddenFields("","","");';
        }
// MTY - LEAVE SYSTEM        
      }
      $colScript .= '</script>';
    }
    if (substr ( $colName, $posDate, 4 ) == 'Date') { // Date => onChange
      $colScript .= '<script type="dojo/connect" event="onChange">';
      $colScript .= '  if (this.value!=null && this.value!="") { ';
      // CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '    formChanged(' . $specificWidgetsToDisabled . ');';
      // Old
      // $colScript .= ' formChanged();';
      // END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '  }';
      $colScript .= '</script>';
    }
    if (! (substr ( $colName, 0, 2 ) == 'id' and strlen ( $colName ) > 2)) { // OTHER => onKeyPress
      $colScript .= '<script type="dojo/method" event="onKeyDown" args="event">'; // V4.2 Changed onKeyPress to onKeyDown because was not triggered
      $colScript .= '  if (isEditingKey(event)) {';
      // CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '    formChanged(' . $specificWidgetsToDisabled . ');';
      // Old
      // $colScript .= ' formChanged();';
      // END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '  }';
      $colScript .= '</script>';
    }
    if ($colName == "idStatus") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      if (property_exists ( $this, 'idle' ) and get_class ( $this ) != 'StatusMail') {
        $colScript .= htmlGetJsTable ( 'Status', 'setIdleStatus', 'tabStatusIdle' );
        $colScript .= '  var setIdle=0;';
        $colScript .= '  var filterStatusIdle=dojo.filter(tabStatusIdle, function(item){return item.id==dijit.byId("idStatus").value;});';
        $colScript .= '  dojo.forEach(filterStatusIdle, function(item, i) {setIdle=item.setIdleStatus;});';
        $colScript .= '  if (setIdle==1) {';
        $colScript .= '    dijit.byId("idle").set("checked", true);';
        $colScript .= '  } else {';
        $colScript .= '    dijit.byId("idle").set("checked", false);';
        $colScript .= '  }';
      }
      if (property_exists ( $this, 'done' )) {
        $colScript .= htmlGetJsTable ( 'Status', 'setDoneStatus', 'tabStatusDone' );
        $colScript .= '  var setDone=0;';
        $colScript .= '  var filterStatusDone=dojo.filter(tabStatusDone, function(item){return item.id==dijit.byId("idStatus").value;});';
        $colScript .= '  dojo.forEach(filterStatusDone, function(item, i) {setDone=item.setDoneStatus;});';
        $colScript .= '  if (setDone==1) {';
        $colScript .= '    dijit.byId("done").set("checked", true);';
        $colScript .= '  } else {';
        $colScript .= '    dijit.byId("done").set("checked", false);';
        $colScript .= '  }';
      }
      if (property_exists ( $this, 'handled' )) {
        $colScript .= htmlGetJsTable ( 'Status', 'setHandledStatus', 'tabStatusHandled' );
        $colScript .= '  var setHandled=0;';
        $colScript .= '  var filterStatusHandled=dojo.filter(tabStatusHandled, function(item){return item.id==dijit.byId("idStatus").value;});';
        $colScript .= '  dojo.forEach(filterStatusHandled, function(item, i) {setHandled=item.setHandledStatus;});';
        $colScript .= '  if (setHandled==1) {';
        $colScript .= '    dijit.byId("handled").set("checked", true);';
        $colScript .= '  } else {';
        $colScript .= '    dijit.byId("handled").set("checked", false);';
        $colScript .= '  }';
      }
      if (property_exists ( $this, 'cancelled' )) {
        $colScript .= htmlGetJsTable ( 'Status', 'setCancelledStatus', 'tabStatusCancelled' );
        $colScript .= '  var setCancelled=0;';
        $colScript .= '  var filterStatusCancelled=dojo.filter(tabStatusCancelled, function(item){return item.id==dijit.byId("idStatus").value;});';
        $colScript .= '  dojo.forEach(filterStatusCancelled, function(item, i) {setCancelled=item.setCancelledStatus;});';
        $colScript .= '  if (setCancelled==1) {';
        $colScript .= '    dijit.byId("cancelled").set("checked", true);';
        $colScript .= '  } else {';
        $colScript .= '    dijit.byId("cancelled").set("checked", false);';
        $colScript .= '  }';
      }
      // CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '    formChanged(' . $specificWidgetsToDisabled . ');';
      // Old
      // $colScript .= ' formChanged();';
      // END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '</script>';
    } else if ($colName == "idResolution") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      if (property_exists ( $this, 'solved' )) {
        $colScript .= htmlGetJsTable ( 'Resolution', 'solved', 'tabResolutionSolved' );
        $colScript .= '  var solved=0;';
        $colScript .= '  var filterResolutionSolved=dojo.filter(tabResolutionSolved, function(item){return item.id==dijit.byId("idResolution").value;});';
        $colScript .= '  dojo.forEach(filterResolutionSolved, function(item, i) {solved=item.solved;});';
        $colScript .= '  if (solved==1) {';
        $colScript .= '    dijit.byId("solved").set("checked", true);';
        $colScript .= '  } else {';
        $colScript .= '    dijit.byId("solved").set("checked", false);';
        $colScript .= '  }';
      }
      // CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '    formChanged(' . $specificWidgetsToDisabled . ');';
      // Old
      // $colScript .= ' formChanged();';
      // END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '</script>';
    } else if ($colName == "idle") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      if (property_exists ( $this, 'idleDateTime' )) {
        $colScript .= '    if (! dijit.byId("idleDateTime").get("value")) {';
        $colScript .= '      var curDate = new Date();';
        $colScript .= '      dijit.byId("idleDateTime").set("value", curDate); ';
        $colScript .= '      dijit.byId("idleDateTimeBis").set("value", curDate); ';
        $colScript .= '    }';
      }
      if (property_exists ( $this, 'idleDate' )) {
        $colScript .= '    if (! dijit.byId("idleDate").get("value")) {';
        $colScript .= '      var curDate = new Date();';
        $colScript .= '      dijit.byId("idleDate").set("value", curDate); ';
        $colScript .= '    }';
      }
      // ADD tLaguerie ticket #396
      if (property_exists ( $this, 'endDate' ) and get_class($this)!='SupplierContract' and get_class($this)!='ClientContract') {
        $colScript .= '    if(! dijit.byId("endDate").get("value")) {';
        $colScript .= '       var curDate = new Date();';
        $colScript .= '       dijit.byId("endDate").set("value", curDate);';
        $colScript .= '    } ';
      }
      // END tLaguerie ticket #396
      /*
       * if (property_exists($this, 'done')) { // Removed : this is now driven by status
       * $colScript .= ' if (! dijit.byId("done").get("checked")) {';
       * $colScript .= ' dijit.byId("done").set("checked", true);';
       * $colScript .= ' }';
       * }
       */
      /*
       * if (property_exists($this, 'handled')) { // Removed : this is now driven by status
       * $colScript .= ' if (! dijit.byId("handled").get("checked")) {';
       * $colScript .= ' dijit.byId("handled").set("checked", true);';
       * $colScript .= ' }';
       * }
       */
      $colScript .= '  } else {';
        // ADD tLaguerie ticket #396
      if (property_exists ( $this, 'endDate' ) and get_class($this)!='SupplierContract' and get_class($this)!='ClientContract') {
        $colScript .= '    dijit.byId("endDate").set("value", null); ';
        $colScript .= '    if (dijit.byId("endDateBis")) dijit.byId("endDateBis").set("value", null); ';
      }
      // END tLaguerie ticket #396
      if (property_exists ( $this, 'idleDateTime' )) {
        $colScript .= '    dijit.byId("idleDateTime").set("value", null); ';
        $colScript .= '    dijit.byId("idleDateTimeBis").set("value", null); ';
      }
      if (property_exists ( $this, 'idleDate' )) {
        $colScript .= '    dijit.byId("idleDate").set("value", null); ';
      }
      $colScript .= '  } ';
      // CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '    formChanged(' . $specificWidgetsToDisabled . ');';
      // Old
      // $colScript .= ' formChanged();';
      // END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '</script>';
      // ADD tLaguerie ticket #396
     }  else if ($colName == "isResource") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';

      if (property_exists ( $this, 'startDate' )) {
        $colScript .= '    if(! dijit.byId("startDate").get("value")) {';
        $colScript .= '       var curDate = new Date();';
        $colScript .= '       dijit.byId("startDate").set("value", curDate);';
        $colScript .= '    } ';
      }
      $colScript .= '} else {';
        if (property_exists ( $this, 'startDate' )) {
          $colScript .= '    dijit.byId("startDate").set("value", null); ';
        }
      $colScript .= '    } ';
      $colScript .= '</script>';
      // END tLaguerie ticket #396
    } else if ($colName == "done") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      if (property_exists ( $this, 'doneDateTime' )) {
        $colScript .= '    if (! dijit.byId("doneDateTime").get("value")) {';
        $colScript .= '      var curDate = new Date();';
        $colScript .= '      dijit.byId("doneDateTime").set("value", curDate); ';
        $colScript .= '      dijit.byId("doneDateTimeBis").set("value", curDate); ';
        $colScript .= '    }';
      }
      if (property_exists ( $this, 'doneDate' )) {
        $colScript .= '    if (! dijit.byId("doneDate").get("value")) {';
        $colScript .= '      var curDate = new Date();';
        $colScript .= '      dijit.byId("doneDate").set("value", curDate); ';
        $colScript .= '    }';
      }
      if (property_exists ( $this, 'handled' )) {
        $colScript .= '    if (! dijit.byId("handled").get("checked")) {';
        $colScript .= '      dijit.byId("handled").set("checked", true);';
        $colScript .= '    }';
      }
      $colScript .= '  } else {';
      if (property_exists ( $this, 'doneDateTime' )) {
        $colScript .= '    dijit.byId("doneDateTime").set("value", null); ';
        $colScript .= '    dijit.byId("doneDateTimeBis").set("value", null); ';
      }
      if (property_exists ( $this, 'doneDate' )) {
        $colScript .= '    dijit.byId("doneDate").set("value", null); ';
      }
      if (property_exists ( $this, 'idle' )) {
        $colScript .= '    if (dijit.byId("idle").get("checked")) {';
        $colScript .= '      dijit.byId("idle").set("checked", false);';
        $colScript .= '    }';
      }
      $colScript .= '  } ';
      // CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '    formChanged(' . $specificWidgetsToDisabled . ');';
      // Old
      // $colScript .= ' formChanged();';
      // END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '</script>';
    } else if ($colName == "handled") {
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      if (property_exists ( $this, 'handledDateTime' )) {
        $colScript .= '    if ( ! dijit.byId("handledDateTime").get("value")) {';
        $colScript .= '      var curDate = new Date();';
        $colScript .= '      dijit.byId("handledDateTime").set("value", curDate); ';
        $colScript .= '      dijit.byId("handledDateTimeBis").set("value", curDate); ';
        $colScript .= '    }';
      }
      if (property_exists ( $this, 'handledDate' )) {
        $colScript .= '    if (! dijit.byId("handledDate").get("value")) {';
        $colScript .= '      var curDate = new Date();';
        $colScript .= '      dijit.byId("handledDate").set("value", curDate); ';
        $colScript .= '    }';
      }
      if (property_exists ( $this, 'isUnderConstruction' )) {
        $colScript .= '      if (dijit.byId("isUnderConstruction")) dijit.byId("isUnderConstruction").set("checked", false);';
      }
      $colScript .= '  } else {';
      if (property_exists ( $this, 'handledDateTime' )) {
        $colScript .= '    dijit.byId("handledDateTime").set("value", null); ';
        $colScript .= '    dijit.byId("handledDateTimeBis").set("value", null); ';
      }
      if (property_exists ( $this, 'handledDate' )) {
        $colScript .= '    dijit.byId("handledDate").set("value", null); ';
      }
      if (property_exists ( $this, 'done' )) {
        $colScript .= '    if (dijit.byId("done").get("checked")) {';
        $colScript .= '      dijit.byId("done").set("checked", false);';
        $colScript .= '    }';
      }
      if (property_exists ( $this, 'idle' )) {
        $colScript .= '    if (dijit.byId("idle").get("checked")) {';
        $colScript .= '      dijit.byId("idle").set("checked", false);';
        $colScript .= '    }';
      }
      $colScript .= '  } ';
      // CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '    formChanged(' . $specificWidgetsToDisabled . ');';
      // Old
      // $colScript .= ' formChanged();';
      // END CHANGE BY Marc TABARY - 2017-03-06 - ALLOW DISABLED SPECIFIC WIDGET
      $colScript .= '</script>';
    }
    
    // Krowry
    if (substr ( $colName, - 9 ) == "StartDate" or substr ( $colName, - 7 ) == "EisDate") { // If change start date
      $end = str_replace ( array('EisDate', 'StartDate'), array('EndDate', 'EndDate'), $colName );
      $start = $colName;
      if (property_exists ( $this, $end )) {
        if (self::is_subclass_of ( $this, 'PlanningElement' )) {
          $end = get_class ( $this ) . '_' . $end;
          $start = get_class ( $this ) . '_' . $start;
          $duration = get_class ( $this ) . '_' . str_replace ( 'StartDate', 'Duration', $colName );
        }
        $colScript .= '<script type="dojo/connect" event="onChange" args="evt">';
        $colScript .= "if(this.value){";
        $colScript .= "  var end = dijit.byId('$end');"; // $end will be replaced by value as enclosed by "
        $colScript .= "  if(end){";
        $colScript .= "    var dtStart = dijit.byId('$start').get('value'); "; // => retrieve date for startDate
        $colScript .= "    end.constraints.min=dtStart;"; // Set constraint
        $colScript .= "    end.set('dropDownDefaultValue',dtStart);";
        // $colScript .= " if (! end.get('value') ) {";
        // if (!substr($colName, -7)=="EisDate") $colScript .= " end.set('value',dtStart);";
        // if(isset($duration)){
        // $colScript .= " if (dijit.byId('$duration')) {";
        // $colScript .= " dijit.byId('$duration').set('value',1);";
        // $colScript .= " }";
        // }
        // $colScript .= " }";
        $colScript .= " }";
        $colScript .= "}";
        $colScript .= '</script>';
      }
    }
    
    return $colScript;
  }
  
  // ============================================================================**********
  // MISCELLANOUS FUNCTIONS
  // ============================================================================**********
  
  /**
   * =========================================================================
   * Draw a specific item for a given class.
   * Should always be implemented in the corresponding class.
   * Here is alway an error.
   *
   * @param $item the
   *          item
   * @return a message to draw (to echo) : always an error in this class,
   *         must be redefined in the inherited class
   */
  public function drawSpecificItem($item) {
    return "No specific item " . $item . " for object " . get_class ( $this );
  }

  public function drawCalculatedItem($item) {
    return "No calculated item " . $item . " for object " . get_class ( $this );
  }

  /**
   * =========================================================================
   * Indicate if a property of is translatable
   *
   * @param $col the
   *          nale of the property
   * @return a boolean
   */
  public function isFieldTranslatable($col) {
    $testField = '_is' . ucfirst ( $col ) . 'Translatable';
    if (isset ( $this->{$testField} )) {
      if ($this->{$testField}) {
        return true;
      } else {
        return false;
      }
    }
  }

  /**
   * =========================================================================
   * control data corresponding to Model constraints, before saving an object
   *
   * @param
   *          void
   * @return "OK" if controls are good or an error message
   *         must be redefined in the inherited class
   */
  public function control() {
    // traceLog('control (for ' . get_class($this) . ' #' . $this->id . ')');
    global $cronnedScript, $loginSave, $mode, $canForceClose;
    $user = getSessionUser ();
    $arrayExtraRequired = $this->getExtraRequiredFields ();
    $arrayExtraHidden = $this->getExtraHiddenFields ();
    $result = "";
    $right = "";
    // Manage Exceptions
    if (get_class ( $this ) == 'Alert' or get_class ( $this ) == 'Mail' or get_class ( $this ) == 'MailToSend' 
     or get_class ( $this ) == 'Audit' or get_class ( $this ) == 'AuditSummary' or get_class ( $this ) == 'ColumnSelector'
     or get_class ( $this ) == 'ProjectSituation' or get_class ( $this ) == 'ProjectSituationExpense' or get_class ( $this ) == 'ProjectSituationIncome'   ) {
      $right = 'YES';
    } else if (isset ( $cronnedScript ) and $cronnedScript == true) { // Cronned script can do everything
      $right = 'YES';
    } else if (isset ( $loginSave ) and $loginSave == true) { // User->save during autenticate can do everything
      $right = 'YES';
    } else if (get_class ( $this ) == 'User') { // User can change his own data (to be able to change password)
      if (getSessionUser ()->id or (getSessionUser ()->name and getSessionUser ()->isLdap and getSessionUser ()->name = $this->name)) {
        $usr = getSessionUser ();
        if ($this->id == $usr->id) {
          $right = 'YES';
        }
      }
    } else if (get_class ( $this ) == 'Affectation' and property_exists ( $this, '_automaticCreation' ) and $this->_automaticCreation) {
      $right = 'YES';
    }
    if ($right != 'YES' and get_class ( $this ) == 'Project') {
      if ($this->id) {
        $right = securityGetAccessRightYesNo ( 'menu' . get_class ( $this ), 'update', $this );
      } else {
        $right = securityGetAccessRightYesNo ( 'menu' . get_class ( $this ), 'create' );
      }
      if ($right == "YES" and $this->idProject) {
        $old = new Project ( $this->id, true );
        if ($old->idProject != $this->idProject) { // Can change project only if has management rights to new one
          $proj = new Project ( $this->idProject, true );
          $right = securityGetAccessRightYesNo ( 'menuProject', 'update', $proj );
        }
      }
    } else if ($right != 'YES' and get_class ( $this ) == 'Affectation') {
      $prj = new Project ( $this->idProject, true );
      $right = securityGetAccessRightYesNo ( 'menuProject', 'update', $prj );
    } else if ($right != 'YES') {
      $right = securityGetAccessRightYesNo ( 'menu' . get_class ( $this ), (($this->id) ? 'update' : 'create'), $this, $user );
    }
    if ($right != 'YES') {
      $result .= '<br/>' . i18n ( 'error' . (($this->id) ? 'Update' : 'Create') . 'Rights' );
      $result .= ' <span style="font-style:italic">('.i18n(get_class($this)).(($this->id)?' #'.$this->id:'').')</span>';
      return $result;
    }
    $isCopy = false;
    if (property_exists ( $this, 'idStatus' ) and $this->idStatus) {
      $status = new Status ( $this->idStatus );
      if ($status->isCopyStatus) {
        $isCopy = true;
      }
    }
    foreach ( $this as $col => $val ) {
      $dataType = $this->getDataType ( $col );
      $dataLength = $this->getDataLength ( $col );
      if ($col=='idBudgetItem' and $val) {
        $testBudget=new Budget($val);
        if ($testBudget->elementary!=1) {
          $result.='<br/>'.i18n('errorNotBudgetItem');
        }
      }
      if (substr ( $col, 0, 1 ) != '_') {
        if (ucfirst ( $col ) == $col and is_object ( $val )) {
          $subResult = $val->control ();
          if ($subResult != 'OK') {
            $result .= $subResult;
          }
        } else {
          // check if required
          if ((strpos ( $this->getFieldAttributes ( $col ), 'required' ) !== false or array_key_exists ( $col, $arrayExtraRequired )) 
              and ! $isCopy and !in_array( $col, $arrayExtraHidden ) ) {
            if ($col == 'idResource' and ! trim ( $this->idResource ) and $user->isResource and Parameter::getGlobalParameter ( 'setResponsibleIfNeeded' ) != 'NO') {
              $this->idResource = $user->id;
              $val = $this->idResource;
            }
            //if ( ( ! $val and $val !== 0) or trim ( $val ) == '') {
            if ( ( ! $val) or trim ( $val ) == '') { // Since PHP 7.1, all numeric fields have default value zero and cannot be null  
              $result .= '<br/>'. i18n ( 'messageMandatory', array($this->getColCaption ( $col )) );
            }
          }
          if ($val and strpos($this->getFieldAttributes($col),'unique')!==false) {          
            $where='UPPER('.$this->getDatabaseColumnName($col).")=".Sql::str(strtoupper($val));
            if ($this->id) $where.=" and id<>".$this->id;
            $count=$this->countSqlElementsFromCriteria(null,$where);
            if ($count>0) {
              $result .= '<br/>' . i18n ( 'messageUnique', array(i18n('col'.ucfirst($col)),$val) );
            }
          }
          if ($dataType == 'datetime') {
            if (strlen ( $val ) == 9) {
              $result .= '<br/>' . i18n ( 'messageDateMandatoryWithTime', array(i18n ( 'col' . ucfirst ( $col ) )) );
            }
          }
          if ($dataType == 'date' and $val != '') {
            if (strlen ( $val ) != 10 or substr ( $val, 4, 1 ) != '-' or substr ( $val, 7, 1 ) != '-') {
              $result .= '<br/>' . i18n ( 'messageInvalidDateNamed', array(i18n ( 'col' . ucfirst ( $col ) )) );
            }
          }
        }
      }
      if ($val and $col != 'colRefName') {
        if ($dataType == 'varchar') {
          if (strlen ( $val ) > $dataLength) {
            $result .= '<br/>' . i18n ( 'messageTextTooLong', array(i18n ( 'col' . ucfirst ( $col ) ), $dataLength) );
          }
        } else if ($dataType == "int" or $dataType == "decimal") {
          if (trim ( $val ) and ! is_numeric ( $val )) {
            $result .= '<br/>' . i18n ( 'messageInvalidNumeric', array(i18n ( 'col' . ucfirst ( $col ) )."='".$val."'") );
          }
        }
      }
      if ($dataLength > 4000) {
        if ($val == '<div></div>')
          $val = null;
        try {
          if (isTextFieldHtmlFormatted ( $val ))
            $test = strip_tags ( $val );
        } catch ( Exception $e ) {
          $result .= '<br/>' . i18n ( 'messageInvalidHTML', array(i18n ( 'col' . ucfirst ( $col ) )) );
        }
        if (isTextFieldHtmlFormatted ( $val ))
          $val = htmlEncode ( $val, 'formatted' ); // Erase <script tags and erase value if messy tags
      }
    }
    $idType = 'id' . ((get_class ( $this ) == 'TicketSimple') ? 'Ticket' : get_class ( $this )) . 'Type';
    if (property_exists ( $this, $idType )) {
      $type = ((get_class ( $this ) == 'TicketSimple') ? 'Ticket' : get_class ( $this )) . 'Type';
      $objType = new $type ( $this->$idType );
      if (property_exists ( $objType, 'mandatoryDescription' ) and $objType->mandatoryDescription and property_exists ( $this, 'description' )) {
        if (! $this->description) {
          $result = str_replace ( '<br/>' . i18n ( 'messageMandatory', array($this->getColCaption ( 'description' )) ), '', $result );
          $result .= '<br/>' . i18n ( 'messageMandatory', array($this->getColCaption ( 'description' )) );
        }
      }
      if (property_exists ( $objType, 'mandatoryResourceOnHandled' ) and $objType->mandatoryResourceOnHandled and property_exists ( $this, 'idResource' ) and property_exists ( $this, 'handled' )) {
        if ($this->handled and ! trim ( $this->idResource )) {
          if ($user->isResource and Parameter::getGlobalParameter ( 'setResponsibleIfNeeded' ) != 'NO') {
            $this->idResource = $user->id;
          } else {
            $result = str_replace ( '<br/>' . i18n ( 'messageMandatory', array($this->getColCaption ( 'idResource' )) ), '', $result );
            $result .= '<br/>' . i18n ( 'messageMandatory', array($this->getColCaption ( 'idResource' )) );
          }
        }
      }
      //ADD qCazelles - Ticket #53
      if (property_exists ( $objType, 'mandatoryResourceOnHandled' ) and $objType->mandatoryResourceOnHandled and property_exists ( $this, 'idResource' ) and property_exists ( $this, 'isStarted' )) {
        if ($this->isStarted and ! trim ( $this->idResource )) {
          if ($user->isResource and Parameter::getGlobalParameter ( 'setResponsibleIfNeeded' ) != 'NO') {
            $this->idResource = $user->id;
          } else {
            $result = str_replace ( '<br/>' . i18n ( 'messageMandatory', array($this->getColCaption ( 'idResource' )) ), '', $result );
            $result .= '<br/>' . i18n ( 'messageMandatory', array($this->getColCaption ( 'idResource' )) );
          }
        }
      }
      //END ADD qCazelles - Ticket #53
      if (property_exists ( $objType, 'mandatoryResultOnDone' ) and $objType->mandatoryResultOnDone and property_exists ( $this, 'result' ) and property_exists ( $this, 'done' )) {
        if ($this->done and ! $this->result) {
          $result = str_replace ( '<br/>' . i18n ( 'messageMandatory', array($this->getColCaption ( 'result' )) ), '', $result );
          $result .= '<br/>' . i18n ( 'messageMandatory', array($this->getColCaption ( 'result' )) );
        }
      }
    }
    
    // Gautier #1816
    if (property_exists($this,get_class($this).'PlanningElement')) {
      $classe = get_class ( $this );
      $classeType = $classe . 'Type';
      $idClasseType = 'id' . $classeType;
      $obj = get_class ( $this ) . "PlanningElement";
      $type1 = new $classe ( $this->id );
      // passing to done
      $old = $this->getOld ();
      if ($this->done and ! $old->done) {
        $type2 = new $classeType ( $type1->$idClasseType );
        // checkbox lockNoLeftOnDone checked
        if ($type2->lockNoLeftOnDone == 1) {
          $pe = new $obj ();
          $crit = array('refType' => $classe, 'refId' => $this->id);
          $peLst = $pe->getSqlElementsFromCriteria ( $crit );
          foreach ( $peLst as $pe ) {
            // left work
            if ($pe->leftWork != 0) {
              // error message
              $result .= '<br/>' . i18n ( "NoLeftOnDone" );
            }
          }
        }
      }
    }
    // Particular case for Ticket
    if (property_exists($this,'WorkElement')) {
      $classe = 'Ticket';
      $classeType = 'TicketType';
      $idClasseType = 'id' . $classeType;
      $obj = "WorkElement";
      $type1 = new $classe ( $this->id );
      // passing to done
      $old = $this->getOld ();
      if ($this->done and ! $old->done) {
        $type2 = new $classeType ( $type1->$idClasseType );
        // checkbox lockNoLeftOnDone checked
        if ($type2->lockNoLeftOnDone == 1) {
          $we = new WorkElement ();
          $crit = array('refType' => $classe, 'refId' => $this->id);
          $weLst = $we->getSqlElementsFromCriteria ( $crit );
          foreach ( $weLst as $we ) {
            // left work
            if ($we->leftWork != 0) {
              // error message
              $result .= '<br/>' . i18n ( "NoLeftOnDone" );
            }
          }
        }
      }
    } // end #1816
    // Control for Closed item that all items are closed
    if (property_exists ( $this, 'idle' ) and $this->idle and $this->id) { // #1690 : should be possible to import closed items
      $relationShip = self::$_closeRelationShip;
      if (array_key_exists ( get_class ( $this ), $relationShip )) {
        $objects = '';
        $error = false;
        //ajout de mehdi
        //ticket #1754
        $canForceClose = false;
        $user = getSessionUser ();
        $crit = array('idProfile' => $user->getProfile ( $this ), 'scope' => 'canForceClose');
        $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', $crit );
        if ($habil and $habil->id and $habil->rightAccess == '1') {
        	$canForceClose = true;
        }
        //end
        foreach($relationShip[get_class($this)] as $object => $mode) {
          if($canForceClose and $mode=='control'){
            $mode = "confirm";
          }
          if (($mode == 'control' or $mode == 'confirm') and property_exists ( $object, 'idle' )) {
            $where = null;
            $obj = new $object ();
            $crit = array('id' . get_class ( $this ) => $this->id, 'idle' => '0');
            if (property_exists ( $obj, 'refType' ) and property_exists ( $obj, 'refId' )) {
              if (property_exists ( $obj, 'id' . get_class ( $this ) )) {
                $crit = null;
                $where = "(id" . get_class ( $this ) . "=" . $this->id . " or (refType='" . get_class ( $this ) . "' and refId=" . $this->id . ")) and idle=0";
              } else {
                $crit = array("refType" => get_class ( $this ), "refId" => $this->id, "idle" => '0');
              }
            }
            if ($object == "Dependency") {
              $crit = null;
              $where = "idle=0 and ((predecessorRefType='" . get_class ( $this ) . "' and predecessorRefId=" . $this->id . ")" . " or (successorRefType='" . get_class ( $this ) . "' and successorRefId=" . $this->id . "))";
            }
            if ($object == "Link") {
              $crit = null;
              $where = "idle=0 and ((ref1Type='" . get_class ( $this ) . "' and ref1Id=" . Sql::fmtId ( $this->id ) . ")" . " or (ref2Type='" . get_class ( $this ) . "' and ref2Id=" . Sql::fmtId ( $this->id ) . "))";
            }
            $nb = $obj->countSqlElementsFromCriteria ( $crit, $where );
            if ($nb > 0) {
              if ($mode == "control")
                $error = true;
              if ($mode == "confirm" and self::isSaveConfirmed()) {
                // If mode confirm and message of confirmation occured : OK
              } else {
                $objects .= "<br/>&nbsp;-&nbsp;" . i18n ( $object ) . " (" . $nb . ")";
                if ($nb < 10) {
                  $objs = $obj->getSqlElementsFromCriteria ( $crit, false, $where );
                  $objects .= ' =>';
                  foreach ( $objs as $o ) {
                    $objects .= ' #' . $o->id;
                  }
                }
              }
            }
          }
        }
        if ($objects != "") {
          if ($error) {
            $result .= "<br/>" . i18n ( "errorControlClose" ) . $objects;
          } else {
            $result .= '<input type="hidden" id="confirmControl" value="save" /><br/>' . i18n ( "confirmControlSave" ) . $objects;
          }
        }
      }
    }
    // control Workflow
    $class = get_class ( $this );
    $old = new $class ( $this->id );
    $fldType = 'id' . $class . 'Type';
    
    // When reopening item control that parent is not still closed
    if (property_exists($this,'idle') and $this->id and $this->idle==0 and $old->idle==1 and !self::is_a($this,'PlanningElement') and !self::is_a($this,'WorkElement')) {
      if (property_exists($this,'idActivity') and $this->idActivity and $class!='Ticket' and $class!='TicketSimple') {
        $act=new Activity($this->idActivity,true);
        if ($act->idle==1) {
          $msg="<br/>$class".i18n("errorReopenControl",array(i18n('Activity').' #'.$this->idActivity));
          if (strpos($result,$msg)===false) $result.=$msg;
        }
      }
      if (property_exists($this,'idProject') and $this->idProject) {
        $prj=new Project($this->idProject,true);
        if ($prj->idle==1) {
          $msg="<br/>".i18n("errorReopenControl",array(i18n('Project').' #'.$this->idProject));
          if (strpos($result,$msg)===false) $result.=$msg;
        }
      }
    } 
    
    if (property_exists ( $class, 'idStatus' ) and property_exists ( $class, $fldType ) and trim ( $old->idStatus ) and trim ( $old->$fldType ) and (trim ( $old->idStatus ) != trim ( $this->idStatus ) or trim ( $old->$fldType ) != trim ( $this->$fldType )) and $old->id and $class != 'Document') {
      $oldStat = new Status ( $old->idStatus );
      $statList = SqlList::getList ( 'Status' );
      $firstStat = key ( $statList );
      if (! $oldStat->isCopyStatus and ($this->idStatus != $old->idStatus or $this->idStatus != $firstStat)) {
          $idProfile = getSessionUser ()->getProfile ( $this );
// ELIOTT - LEAVE SYSTEM          
          // In fact, leaveType and EmploymentContractType aren't standarts Type. They are objects in model/
          if($fldType==="idLeaveType"){
            $type = new LeaveType($this->$fldType);
          }else if($fldType==="idEmploymentContractType"){
            $type = new EmploymentContractType($this->$fldType);
          }else{
            $type = new Type ( $this->$fldType );
          }
            // For Leave System and Leave :
            //   - Leave Admin or 
            //   - Manager of Employee or
            //   - Employee of the leave 
            //   can see status
          if (isLeavesSystemActiv() and $class=='Leave') {
            if (isLeavesAdmin() or isManagerOfEmployee(getSessionUser()->id, $this->idEmployee) or
                (getSessionUser()->isEmployee==1 and $this->idEmployee == getSessionUser()->id)
               ) {
                $theProfile = getFirstADMProfile();
                if ($theProfile!=null) {
                    $idProfile = $theProfile->id;
                }
            }
          }
// ELIOTT - LEAVE SYSTEM          
        
        $crit = array(
            'idWorkflow' => $type->idWorkflow, 
            'idStatusTo' => $this->idStatus, 
            'idProfile' => $idProfile);
        if (trim ( $old->idStatus ) != trim ( $this->idStatus )) {
          $crit ['idStatusFrom'] = $old->idStatus;
        }
        $ws = new WorkflowStatus ();
        $wsList = $ws->getSqlElementsFromCriteria ( $crit );
        $allowed = false;
        foreach ( $wsList as $ws ) {
// MTY - LEAVE SYSTEM          
             // For Leave System and Leave :
            //   - Employee of the leave 
            //   status that has not id = 1 and with setSubmittedLeave = 0 and setAcceptedLeave = 1 or setRejectedLeave = 1
            // are not allowed
          if (isLeavesSystemActiv() and $class=='Leave' and $ws->id <> 1) {
            if (getSessionUser()->isEmployee==1 and $this->idEmployee == getSessionUser()->id) {
                $theStatus = new Status($ws->id);
                if  ($theStatus->setSubmittedLeave==0 and ($theStatus->setRejectedLeave==1 or $theStatus->setAcceptedLeave==1)) {
                    $ws->allowed = false;
                }
            }
          }
// MTY - LEAVE SYSTEM          
          if ($ws->allowed) {
            $allowed = true;
            break;
          }
        }
        if (! $allowed) {
          $result .= "<br/>" . i18n ( "errorWorflow" );
        }
      }
    }
    // PlugIn Management
    $list = Plugin::getEventScripts ( 'control', get_class ( $this ) );
    foreach ( $list as $script ) {
      require $script; // execute code
    }
    if ($result == "") {
      $result = 'OK';
    }
    return $result;
  }

  /**
   * =========================================================================
   * control data corresponding to Model constraints, before deleting an object
   *
   * @param
   *          void
   * @return "OK" if controls are good or an error message
   *         must be redefined in the inherited class
   */
  public function deleteControl() {
    $result = "";
    $objects = "";
    $realWorks = "";
    $plannedWorks = "";
    $right = securityGetAccessRightYesNo ( 'menu' . get_class ( $this ), 'delete', $this );
    if (get_class ( $this ) == 'Alert' or get_class ( $this ) == 'Mail' or get_class ( $this ) == 'MailToSend' 
     or get_class ( $this ) == 'Audit' or get_class ( $this ) == 'AuditSummary' or get_class ( $this ) == 'ColumnSelector'
     or get_class ( $this ) == 'ProjectSituation' or get_class ( $this ) == 'ProjectSituationExpense' or get_class ( $this ) == 'ProjectSituationIncome') {
      $right = 'YES';
    }
    if ($right != 'YES') {
      $result .= '<br/>' . i18n ( 'errorDeleteRights' );
      $result .= ' <span style="font-style:italic">('.i18n(get_class($this)).' #'.$this->id.')</span>';
      return $result;
    }
    $relationShip = self::$_relationShip;
    $canForceDelete = false;
    $canDeleteRealWork=false;
    if (getSessionUser ()->id) {
      $user = getSessionUser ();
      $crit = array('idProfile' => $user->getProfile ( $this ), 'scope' => 'canForceDelete');
      $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', $crit );
      if ($habil and $habil->id and $habil->rightAccess == '1') {
        $canForceDelete = true;
      }
      $crit = array('idProfile' => $user->getProfile ( $this ), 'scope' => 'canDeleteRealWork');
      $habil = SqlElement::getSingleSqlElementFromCriteria ( 'HabilitationOther', $crit );
      if ($habil and $habil->id and $habil->rightAccess == '1') {
      	$canDeleteRealWork = true;
      }
    }
    if (array_key_exists ( get_class ( $this ), $relationShip )) {
      $relations = $relationShip [get_class ( $this )];
      $error = false;
      foreach ( $relations as $object => $mode ) {
        if ($mode == "control" and ($canForceDelete or $canDeleteRealWork)) {
          $mode = "confirm";
        } else if ($mode == "controlStrict" and !$canDeleteRealWork) {
          $mode = "control";
        }
        if ($mode == "control" or $mode == "confirm") {
          $where = null;
          $obj = new $object ();
          $crit = array('id' . get_class ( $this ) => $this->id);
          if (get_class ( $this )=='AccessProfileNoProject') {
            $crit = array('idAccessProfile'=> $this->id);
          }
          if (self::is_a ( $this, 'Version' )) {
            $crit = null;
            $where = "(1=0";
            $arrayVersion = array(
                'idVersion', 
                'idTargetVersion', 
                'idTargetProductVersion', 
                'idTargetComponentVersion', 
                'idOriginalVersion', 
                'idOriginalProductVersion', 
                'idOriginalComponentVersion');
            foreach ( $arrayVersion as $vers ) {
              if (property_exists ( $obj, $vers )) {
                $where .= " or " . $obj->getDatabaseColumnName ( $vers ) . "=" . $this->id;
              }
            }
            $where .= ")";
          } else if (property_exists ( $obj, 'refType' ) and property_exists ( $obj, 'refId' )) {
            if (property_exists ( $obj, 'id' . get_class ( $this ) )) {
              $crit = null;
              $where = "id" . get_class ( $this ) . "=" . $this->id . " or (refType='" . get_class ( $this ) . "' and refId=" . $this->id . ")";
            } else {
              $crit = array("refType" => get_class ( $this ), "refId" => $this->id);
            }
          }
          if ($object == "Dependency") {
            $crit = null;
            $where = "(predecessorRefType='" . get_class ( $this ) . "' and predecessorRefId=" . $this->id . ")" . " or (successorRefType='" . get_class ( $this ) . "' and successorRefId=" . $this->id . ")";
          } else if ($object == "Link") {
            $crit = null;
            $where = "(ref1Type='" . get_class ( $this ) . "' and ref1Id=" . Sql::fmtId ( $this->id ) . ")" . " or (ref2Type='" . get_class ( $this ) . "' and ref2Id=" . Sql::fmtId ( $this->id ) . ")";
          } else if (substr ( $object, - 4 ) == 'Type' and property_exists($object, 'scope')) {
            $scope = substr ( $object, 0, strlen ( $object ) - 4 );
            $crit ['scope'] = $scope;
          }
          $nb = $obj->countSqlElementsFromCriteria ( $crit, $where );
          if ($nb > 0) {
            if ($mode == "control")
              $error = true;
            if ($mode == "confirm" and self::isDeleteConfirmed ()) {
              // If mode confirm and message of confirmation occured : OK
            } else {
              $objects .= "<br/>&nbsp;-&nbsp;" . i18n ( $object ) . " (" . $nb . ")";
              if(property_exists(get_class ($this), get_class ($this).'PlanningElement')){
                $ple = get_class ($this).'PlanningElement';
              	$planningMode = new PlanningMode($this->$ple->idPlanningMode);
              	if($planningMode->code=='MAN' and $this->$ple->plannedWork){
              		$plannedWorks .= "<br/>&nbsp;-&nbsp;" . i18n ($this->$ple->refType) . " #".$this->$ple->refId." (" .Work::displayWorkWithUnit($this->$ple->plannedWork). ")";
              	}
              }
            }
          }
          if($canDeleteRealWork){
            $objList = $obj->getSqlElementsFromCriteria($crit,null,$where);
            foreach ($objList as $objWork){
              $work = new Work();
              $refType=get_class($objWork);
              $refId=$objWork->id;
              $critWork=array('refType'=>$refType,'refId'=>$refId);
              $workList = $work->getSqlElementsFromCriteria($critWork);
              foreach ($workList as $work){
                $realWorks .="<br/>&nbsp;-&nbsp;".i18n('realWorkElement',array(i18n($work->refType).' #'.$refId,SqlList::getNameFromId('Resource', $work->idResource),$work->displayWorkWithUnit($work->work),htmlFormatDate($work->workDate,true)));
              }
            }
         }
          if (get_class($this)=='Contact' and property_exists($obj,'idSponsor')) { 
            // Also search for sponsor
            $crit = array('idSponsor' => $this->id);
            $nb = $obj->countSqlElementsFromCriteria ( $crit, $where );
            if ($nb > 0) {
              if ($mode == "control")
                $error = true;
              if ($mode == "confirm" and self::isDeleteConfirmed ()) {
                // If mode confirm and message of confirmation occured : OK
              } else {
                $objects .= "<br/>&nbsp;-&nbsp;" . i18n ( $object ) . " (" . $nb . ")";
              }
            }
          }
        }
      }
      if ($objects != "") {
        if ($error) {
          $result .= "<br/>" . i18n ( "errorControlDelete" ) . $objects;
        } else {
          $result .= '<input type="hidden" id="confirmControl" value="delete" /><br/>' . i18n ( "confirmControlDelete" ) . $objects;
          if($canDeleteRealWork){
            $result .= "<br/><br/>".i18n ( "confirmControlDeleteRealWork" ) . $realWorks;
          }
          if($plannedWorks){
          	$result .= "<br/><br/>".i18n ( "confirmControlDeletePlannedWork" ) . $plannedWorks;
          }
        }
      }
    }
    // PlugIn Management
    $list = Plugin::getEventScripts ( 'deleteControl', get_class ( $this ) );
    foreach ( $list as $script ) {
      require $script; // execute code
    }
    if ($result == "") {
      $result = 'OK';
    }
    return $result;
  }

  /**
   * =========================================================================
   * Return the menu string for the object (from its class)
   *
   * @param
   *          void
   * @return a string
   */
  public function getMenuClass() {
    return "menu" . get_class ( $this );
  }

  /**
   * =========================================================================
   * Send a mail on status change (if object is "mailable")
   *
   * @param
   *          void
   * @return status of mail, if sent
   */
  public function sendMailIfMailable($newItem = false, $statusChange = false, $directStatusMail = null, $responsibleChange = false, $noteAdd = false, $attachmentAdd = false, $noteChange = false, $descriptionChange = false, $resultChange = false, $assignmentAdd = false, $assignmentChange = false, $anyChange = false,$affectationAdd = false , $affectationChange = false, $linkAdd = false, $linkDelete = false, $attachments=false) {
    global $cronnedScript, $doNotTriggerAlerts;
    if ($doNotTriggerAlerts==true) return false;
    
    $objectClass = get_class($this);
    $idProject = ($objectClass == 'Project') ? $this->id : ((property_exists ( $this, 'idProject' )) ? $this->idProject : null);
    if ($objectClass == 'TicketSimple') {
      $objectClass = 'Ticket';
    }
    if ($objectClass == 'History' or $objectClass == 'Audit' or $objectClass == 'KpiHistory' or ! in_array($objectClass,SqlList::getListNotTranslated('Mailable'))) {
      return false; // exit : not for History
    }
    
    $canBeSend = true;
    if ($idProject) {
      $canBeSend = ! SqlList::getFieldFromId ( "Project", $idProject, "isUnderConstruction" );
    }
    //KROWRY
    $statusMailList = array();
    $statusMailListOrganized=array();
    $statusMail = new StatusMail ();
    $sender = Parameter::getGlobalParameter ( 'paramMailSender' );
    
    if ($directStatusMail) { // Direct Send Mail
      $statusMailList = array($directStatusMail->id => $directStatusMail);
      $statusMailListOrganized=$statusMailList;
      if (getSessionUser ()->email)
        $sender = getSessionUser ()->email;
    } else if ($canBeSend) {
      if ($objectClass=='Affectation') {
        $mailable=SqlElement::getSingleSqlElementFromCriteria ( 'Mailable', array('name' => 'Project') );
      } else {
        $mailable = SqlElement::getSingleSqlElementFromCriteria ( 'Mailable', array('name' => $objectClass) );
      }
      if (! $mailable or ! $mailable->id) {
        return false; // exit if not mailable object
      }
      $crit = "idle=0";
      $crit .= " and idMailable=" . $mailable->id . " and ( 1=0 ";
      if ($statusChange and property_exists ( $this, 'idStatus' ) and trim ( $this->idStatus )) {
        $any = SqlElement::getSingleSqlElementFromCriteria('EventForMail', array('name'=>'statusChange'));
        $crit .= "  or idStatus=" . $this->idStatus . " or idEventForMail=".$any->id." ";
      }
      if ($responsibleChange) {
        $crit .= " or idEventForMail=1 ";
      }
      if ($noteAdd) {
        $crit .= " or idEventForMail=2 ";
      }
      if ($attachmentAdd) {
        $crit .= " or idEventForMail=3 ";
      }
      if ($noteChange) {
        $crit .= " or idEventForMail=4 ";
      }
      if ($descriptionChange) {
        $crit .= " or idEventForMail=5 ";
      }
      if ($resultChange) {
        $crit .= " or idEventForMail=6 ";
      }
      if ($assignmentAdd) {
        $crit .= " or idEventForMail=7 ";
      }
      if ($assignmentChange) {
        $crit .= " or idEventForMail=8 ";
      }
      if ($anyChange) {
        $crit .= " or idEventForMail=9 ";
      }
      if ($affectationAdd) {
        $crit .= " or idEventForMail=10 ";
      }
      if ($affectationChange) {
        $crit .= " or idEventForMail=11 ";
      }
      if ($linkAdd) {
        $crit .= " or idEventForMail=12 ";
      }
      if ($linkDelete) {
        $crit .= " or idEventForMail=13 ";
      }
      $crit .= ")";
      $statusMailList = $statusMail->getSqlElementsFromCriteria ( null, false, $crit );
      // $statusMailList contains all events compatible with current change.
      // Now, we must resctrict : if several lines exist for same event, we must limit to 1 only (depending on project and/or type 
      $typeName='id'.$objectClass.'Type';
      $proj=null;
      $type=null;
      if(property_exists($this, "idProject")){
        $proj=($objectClass=='Project')?$this->id:$this->idProject;
        $project = new Project($proj);
        $topList=$project->getTopProjectList(true);
      }
      if(property_exists($this, $typeName)){
        $type=$this->$typeName;
      }
      $isSubProj = false;
      foreach ($statusMailList as $stm) {
        $isSubProj = false;
        if($proj and $stm->idProject){
        	if(in_array($stm->idProject,$topList)){
        		//$statusMailListOrganized[$stm->idEventForMail]=$stm;
        		$isSubProj = true;
        	}
        }
        if ($proj and $stm->idProject and $stm->idProject!=$proj and !$isSubProj) { // Does not concern current project, must not apply
          continue;
        }
        if ($type and $stm->idType and $stm->idType!=$type) { // Does not concern current type, must not apply
          continue;
        }
        if (! isset($statusMailListOrganized[$stm->idEventForMail])) { // No other already selected : OK
          $statusMailListOrganized[$stm->idEventForMail]=$stm;
          continue;
        }
        // Now we are treating duplicates (already exists for event, we have another one that may fit, so we must select on Project and / or Type
        if ($proj and $stm->idProject and ($stm->idProject==$proj or $isSubProj)) { // OK, dedicated to correct project (or top one)
          if (! $statusMailListOrganized[$stm->idEventForMail]->idProject) {
            $statusMailListOrganized[$stm->idEventForMail]=$stm;        // Found rule on projet, previous was not
          } else if (array_search($stm->idProject,$topList)<array_search($statusMailListOrganized[$stm->idEventForMail]->idProject,$topList) ) {
            $statusMailListOrganized[$stm->idEventForMail]=$stm;        // Found rule with projet "neared" on parents list than previously stored 
          } else if ($stm->idProject and array_search($stm->idProject,$topList)>array_search($statusMailListOrganized[$stm->idEventForMail]->idProject,$topList) ) { 
            continue;                                                   // Found rule with projet "farther" on parents list than previously stored 
          } else if ($type and $stm->idType and $stm->idType==$type) { 
            $statusMailListOrganized[$stm->idEventForMail]=$stm;        // Same project than previously stored and type match : this is this one !!!!
          } else {
            continue;                                                   // Same project than previously stored and not type match : pass ! (duplicate ?)
          }
        } else { // Not project defined rule, will check on type
          if ($type and $stm->idType and $stm->idType==$type) { // Same type but not same project, replace if previous not on project
            if (!$statusMailListOrganized[$stm->idEventForMail]->idProject) {
              $statusMailListOrganized[$stm->idEventForMail]=$stm;     // Same type and previously stored not on project neither : store
            } else {
              continue;                                                // Not same Same project than previously stored and not type match : pass ! (duplicate ?)
            }
          }
        }
      }
      
    }
    if (count($statusMailListOrganized)== 0 || (! $directStatusMail && ! $canBeSend) ) {
      return false; // exit not a status for mail sending (or disabled)
    }
    //    BEGIN modif gmartin / handle Email templates  Ticket #157
    $destTab = array('basic' => null);    //store the mail adressses and the templates names that will be used to send the mails. replace : $dest
    $emailTemplateTab = array();
    $i = 0;
    foreach ( $statusMailListOrganized as $statusMail ) {
      $emailTemplateTab[$i] = new EmailTemplate($statusMail->idEmailTemplate);
      if ($emailTemplateTab[$i]->name == null) {
        $emailTemplateTab[$i]->name = 'basic';
        $emailTemplateTab[$i]->id = '0';
      }
      $destTab[$emailTemplateTab[$i]->id] = null;
      
      if ($statusMail->idType) {
        if (property_exists ( $this, 'idType' ) and $this->idType != $statusMail->idType) {
          continue; // exist : not corresponding type
        }
        $typeName = 'id' . $objectClass . 'Type';
        if (property_exists ( $this, $typeName ) and $this->$typeName != $statusMail->idType) {
          continue; // exist : not corresponding type
        }
      }
      if ($statusMail->mailToUser == 0 and $statusMail->mailToAccountable == 0 and $statusMail->mailToResource == 0 and $statusMail->mailToProject == 0 and $statusMail->mailToLeader == 0 and $statusMail->mailToContact == 0 and $statusMail->mailToOther == 0 and $statusMail->mailToManager == 0 and $statusMail->mailToAssigned == 0 and $statusMail->mailToSubscribers == 0 and $statusMail->mailToSponsor == 0 and $statusMail->mailToProjectIncludingParentProject == 0 and $statusMail->mailToFinancialResponsible == 0) {
        continue; // exit not a status for mail sending (or disabled)
      }
      if ($statusMail->mailToUser) {
        if (property_exists ( $this, 'idUser' )) {
          $user = new User ( $this->idUser );
          $newDest = "###" . $user->email . "###";
          if ($user->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
            $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
            $destTab[$emailTemplateTab[$i]->id] .= $newDest;
          }
        }
      }
      if ($statusMail->mailToAccountable) {
        if (property_exists ( $this, 'idAccountable' )) {
          $resource = new Resource ( $this->idAccountable );
          $newDest = "###" . $resource->email . "###";
          if ($resource->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
            $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
            $destTab[$emailTemplateTab[$i]->id] .= $newDest;
          }
        }
      }
      if ($statusMail->mailToResource) {
        if (property_exists ( $this, 'idResource' )) {
          $resource = new Resource ( $this->idResource );
          $newDest = "###" . $resource->email . "###";
          if ($resource->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
            $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
            $destTab[$emailTemplateTab[$i]->id] .= $newDest;
          }
        }
      }
      if ($statusMail->mailToSponsor) {
        if (property_exists ( $this, 'idSponsor' )) {
          $sponsor = new Sponsor ( $this->idSponsor );
          $newDest = "###" . $sponsor->email . "###";
          if ($sponsor->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
            $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
            $destTab[$emailTemplateTab[$i]->id] .= $newDest;
          }
        }
      }
      if ($statusMail->mailToProject or $statusMail->mailToLeader or $statusMail->mailToProjectIncludingParentProject) {
        $aff = new Affectation ();
        if ($statusMail->mailToProjectIncludingParentProject) {
          $proj = new Project($idProject); 
          $critWhere="idProject in ".transformValueListIntoInClause($proj->getTopProjectList(true));
          $affList = $aff->getSqlElementsFromCriteria ( null, false, $critWhere);
        } else {
          $crit = array('idProject' => $idProject, 'idle' => '0');
          $affList = $aff->getSqlElementsFromCriteria ( $crit, false );
        }
        if ($affList and count ( $affList ) > 0) {
          foreach ( $affList as $aff ) {
            $resource = new Affectable ( $aff->idResource );
            if ($statusMail->mailToProject or $statusMail->mailToProjectIncludingParentProject) {
              // Change on V4.4.0 oly send mail if user has read access to item
              if ($aff->idResource == getSessionUser ()->id) {
                $usr = getSessionUser ();
              } else {
                $usr = new User ( $aff->idResource );
              }
              if (! $resource->dontReceiveTeamMails) {
                $newDest = "###" . $resource->email . "###";
                if ($resource->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
                    $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
                    $destTab[$emailTemplateTab[$i]->id] .= $newDest;
                }
              }
            }
            if ($statusMail->mailToLeader and ($aff->idProfile or $resource->idProfile)) {
              $profile = ($aff->idProfile) ? $aff->idProfile : $resource->idProfile;
              $prf = new Profile ( $profile );
              if ($prf->profileCode == 'PL') {
                $newDest = "###" . $resource->email . "###";
                if ($resource->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
                  $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
                  $destTab[$emailTemplateTab[$i]->id] .= $newDest;
                }
              }
            }
          }
        }
      }
      if ($statusMail->mailToManager) {
        if (property_exists ( $this, 'idProject' )) {
          $project = new Project ( $idProject );
          $manager = new Affectable ( $project->idResource );
          $newDest = "###" . $manager->email . "###";
          if ($manager->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
            $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
            $destTab[$emailTemplateTab[$i]->id] .= $newDest;
          }
        }
      }
      if ($statusMail->mailToAssigned) {
        $ass = new Assignment ();
        $crit = array('refType' => $objectClass, 'refId' => $this->id);
        $assList = $ass->getSqlElementsFromCriteria ( $crit );
        foreach ( $assList as $ass ) {
          $res = new ResourceAll ( $ass->idResource );
          $newDest = "###" . $res->email . "###";
          if ($res->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
            $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
            $destTab[$emailTemplateTab[$i]->id] .= $newDest;
          }
        }
      }
      if ($statusMail->mailToContact) {
        if (property_exists ( $this, 'idContact' )) {
          $contact = new Contact ( $this->idContact );
          $newDest = "###" . $contact->email . "###";
          if ($contact->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
            $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
            $destTab[$emailTemplateTab[$i]->id] .= $newDest;
          }
        }
      }
      if ($statusMail->mailToSubscribers) {
        $crit ="(refType='".$objectClass."' and refId=".Sql::fmtId($this->id).")";
        if (property_exists($this, 'idProject')) {
          $crit.=" or (refType='Project' and refId=".Sql::fmtId($this->idProject).")";
        }
        $sub = new Subscription ();
        $lstSub = $sub->getSqlElementsFromCriteria ( null, null,$crit );
        foreach ( $lstSub as $sub ) {
          $resource = new Affectable ( $sub->idAffectable );
          $newDest = "###" . $resource->email . "###";
          if ($resource->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
            $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
            $destTab[$emailTemplateTab[$i]->id] .= $newDest;
          }
        }
      }
      if ($statusMail->mailToFinancialResponsible) {
      	if (property_exists ( $this, 'idResponsible' )) {
      		$responsible = new Resource ( $this->idResponsible );
      		$newDest = "###" . $responsible->email . "###";
      		if ($responsible->email and strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
      			$destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
      			$destTab[$emailTemplateTab[$i]->id] .= $newDest;
      		}
      	}
      }
      if ($statusMail->mailToOther) {
        if ($statusMail->otherMail) {
          $otherMail = str_replace ( ';', ',', $statusMail->otherMail );
          $otherMail = str_replace ( ' ', ',', $otherMail );
          $split = explode ( ',', $otherMail );
          foreach ( $split as $adr ) {
            if ($adr and $adr != '') {
              $newDest = "###" . $adr . "###";
              if (strpos ( $destTab[$emailTemplateTab[$i]->id], $newDest ) === false) {
                $destTab[$emailTemplateTab[$i]->id] .= ($destTab[$emailTemplateTab[$i]->id]) ? ', ' : '';
                $destTab[$emailTemplateTab[$i]->id] .= $newDest;
              }
            }
          }
        }
      }
      $i++;
    }

    $msgWithoutDest = 0;      //check if something went wrong in retreiving email adresses
    $j = $i;
    while ($i--) {
      if ($destTab[$emailTemplateTab[$i]->id] == '' or $destTab[$emailTemplateTab[$i]->id] == null or str_replace(array(',',';',' '),'',$destTab[$emailTemplateTab[$i]->id])=='')
        $msgWithoutDest++;
      else 
        $destTab[$emailTemplateTab[$i]->id] = str_replace('###', '', $destTab[$emailTemplateTab[$i]->id]);
    }
    if ($j <= $msgWithoutDest)   {
      traceLog('sendMailIfMailable : Mails without dest');
      return false;         // exit because no adresses
    }
    //    END modif gmartin Ticket #157
    if ($newItem) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleNew' );
    } else if ($noteAdd) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleNote' );
    } else if ($noteChange) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleNoteChange' );
    } else if ($assignmentAdd) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleAssignment' );
    } else if ($assignmentChange) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleAssignmentChange' );
    } else if ($attachmentAdd) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleAttachment' );
    } else if ($statusChange) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleStatus' );
    } else if ($responsibleChange) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleResponsible' );
    } else if ($descriptionChange) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleDescription' );
    } else if ($resultChange) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleResult' );
    } else if ($directStatusMail) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleDirect' );
    } else if ($anyChange) {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleAnyChange' );
    } else if ($affectationAdd) {
       $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleAffectationAdd' );
    } else if ($affectationChange) {
       $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleAffectationChange' );
    } else if ($linkAdd) {
       $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleLinkAdd' );
    } else if ($linkDelete) {
        $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitleLinkDelete' );
    } else {
      $paramMailTitle = Parameter::getGlobalParameter ( 'paramMailTitle' ); // default
    }
    $title = $this->parseMailMessage ( $paramMailTitle );
    $references = $objectClass . "-" . $this->id;
    $message = $this->getMailDetail ();
    if ($directStatusMail and isset ( $directStatusMail->message ) and trim($directStatusMail->message)!='' ) {
      //$emailTemplateTab[0]->template = $this->getMailDetail($directStatusMail->message);
      $emailTemplateTab[0]->template = $this->parseMailMessage ( $directStatusMail->message ) . '<br/><br/>' . $emailTemplateTab[0]->template;
    }
    //    BEGIN add gmartin Ticket #157
    $groupMails=Parameter::getGlobalParameter('mailGroupActive');
    if ($directStatusMail) $groupMails='NO';
    $resultMail=array();
    while ($j--) {
      if ($groupMails=='YES') {
        $temp=new MailToSend();
        $temp->idUser=getCurrentUserId();
        $temp->refType=$objectClass;
        $temp->refId=$this->id;
        $temp->idEmailTemplate=$emailTemplateTab[$j]->id;
        $temp->template=$emailTemplateTab[$j]->name;
        if ($emailTemplateTab[$j]->title) {
          $temp->title=$this->getMailDetailFromTemplate($emailTemplateTab[$j]->title,null,true);
        } else {
          $temp->title=$title;
        }
        $temp->dest=$destTab[$emailTemplateTab[$j]->id];
        if(Parameter::getUserParameter('notReceiveHisOwnEmails')=='YES' and ! $cronnedScript){
          $tabDest=explode(",", $temp->dest);
          if(count($tabDest)>0){
            $curUser=new Affectable(getSessionUser()->id);
            foreach ($tabDest as $id=>$mail){
              if(trim($mail)==$curUser->email){
                unset($tabDest[$id]);
              }
            }
          }
          $temp->dest=implode(",", $tabDest);
        }
        $temp->recordDateTime=date('Y-m-d H:i:s');
        if (trim(str_replace(array(',',';',' '),'',$temp->dest))) {
          $tempRes=$temp->save();
          $resultMail[]='TEMP';
        }
      } else { 
        $tempAttach='No';
        $erroSize='';
        //florent ticket 4442
        $directory=Parameter::getGlobalParameter('paramAttachmentDirectory');
        $lstAtt= array();
        $addAttachToMessage='';
        $notFound=' not found ';
        if(!empty($attachments) and Parameter::getGlobalParameter('paramMailerType')=='phpmailer'){
          $c=0;
          $addAttachToMessage="<table style='font-size:11pt;min-width: 800px; max-width: 1200px; width:95%;font-family:Verdana,Arial,Helvetica,sans-serif;'>
                                <tr><td colspan='3' style='background:#606062;color:#ffffff;text-align:center;font-size:14pt;font-weight:normal;width:100%'><div >".htmlEncode( i18n('fileAttachment'))."</div></td></tr>";
          foreach ($attachments as $val){
            $c++;
            $addAttachToMessage.="<tr><td colspan='2' style='background:#ffffff;font-weight:bold;text-align:right;width:30%;min-width:30%;vertical-align:top;white-space:nowrap;padding-bottom:10px;'><div>".i18n('col'.ucfirst($val[1]))."</div></td>";
            if($val[1]=='file'){
              $att=new Attachment($val[0]);
              $lstAtt[$att->fileName]=str_replace('${attachmentDirectory}',$directory, $att->subDirectory).$att->fileName;
              if( file_exists(str_replace('${attachmentDirectory}',$directory, $att->subDirectory).$att->fileName) and $lstAtt[$att->fileName]!=''){
                $addAttachToMessage .="<td colspan='2' style='background:#ffffff;text-align:left;width:70%;padding-left:20px;padding-bottom:10px;vertical-align:top;padding-bottom:10px;'><div>".$att->fileName."</div></td></tr>";
              }else{
                $addAttachToMessage .="<td colspan='2' ><div style='background:#ffffff;text-align:left;width:70%;padding-left:20px;padding-bottom:10px;vertical-align:top;padding-bottom:10px;color:red;'>".$att->fileName.$notFound."</div></td></tr>";
              }
            }else{
              $doc=new DocumentVersion($val[0]);
              $lstAtt[$doc->fileName]=$doc->getUploadFileName();
              if( file_exists($doc->getUploadFileName()) and $lstAtt[$doc->fileName]!=''){
                $addAttachToMessage.= "<td colspan='2' ><div style='background:#FFFFFF;text-align: left;'>".$doc->fileName."</div></td></tr>";
              }else{
                $addAttachToMessage.= "<td colspan='2' ><div style='background:#FFFFFF;text-align: left;color:red;'>".$doc->fileName.$notFound."</div></td></tr>";
              }
            }
          }
          $addAttachToMessage.="</table>";
          $attachments=$lstAtt;
        }
        //
        if ($emailTemplateTab[$j]->name == 'basic') {
          $emailTemplateTab[$j]->template = $this->getMailDetail();
          if ($directStatusMail and isset ( $directStatusMail->message )) {
            //florent
            $emailTemplateTab[$j]->template =$this->getMailDetail( $directStatusMail->message);
          }
          $emailTemplateTab[$j]->title = $title;
          $emailTemplateTab[$j]->template = '<html><head><title>' . $title .
              '</title></head><body style="font-family: Verdana, Arial, Helvetica, sans-serif;">' .
              $emailTemplateTab[$j]->template .$addAttachToMessage. '</body></html>';
        } else {
          //florent #4442
          $tempAttach='Yes';
          $maxSizeAttachment=Parameter::getGlobalParameter('paramAttachmentMaxSizeMail');
          if($maxSizeAttachment==''){
            $maxSizeAttachment=0;
          }
          if(strpos($emailTemplateTab[$j]->template,'${lastAttachment}')!==false){
            $valueAttach=explode('/',$this->searchLastAttachmentMailable());
            if($valueAttach[0]!=''){
              $fileSize=$valueAttach[1];
              if($fileSize < $maxSizeAttachment){
                $attachments[]=explode('_',$valueAttach[0]);
                foreach ($attachments as $val){
                  $att=new Attachment($val[0]);
                  $lstAtt[$att->fileName]=str_replace('${attachmentDirectory}',$directory, $att->subDirectory).$att->fileName;
                  $attachments=$lstAtt;
                }
              }else{
                return array('result' => 'ErrorSize','dest'=>"");
              }
            }
          }else if(strpos($emailTemplateTab[$j]->template,'${allAttachments}')!==false){
            $res=$this->searchAllAttachmentsMailable($maxSizeAttachment);
            $attachments=$res['attachments'];
            $erroSize=($res['result']!='Ok')?i18n('toLargeAllNotAttached'):'';
            if(!empty($attachments)){
              foreach ($attachments as $val){
                $att=new Attachment($val[0]);
                $lstAtt[$att->fileName]=str_replace('${attachmentDirectory}',$directory, $att->subDirectory).$att->fileName;
              }
              $attachments=$lstAtt;
            }else{
              $erroSize='';
            }
          }
          //
          $emailTemplateTab[$j]->template = $this->getMailDetailFromTemplate($emailTemplateTab[$j]->template);
          if ($emailTemplateTab[$j]->title == '' or $emailTemplateTab[$j]->title == null){
            $emailTemplateTab[$j]->title = $title;
          }else {
            $emailTemplateTab[$j]->title = $this->getMailDetailFromTemplate($emailTemplateTab[$j]->title,null,true);
          }
        }
        if (trim(str_replace(array(',',';',' '),'',$destTab[$emailTemplateTab[$j]->id]))) {
          $resultMail[] = sendMail($destTab[$emailTemplateTab[$j]->id], $emailTemplateTab[$j]->title,
            $emailTemplateTab[$j]->template,
            $this, null, $sender, null, null, $references,false,false,$attachments,$erroSize,$tempAttach);
        }
      }
      
    }
    //END add gmartin 
    if ($directStatusMail) {
      $idTemplate=(trim($directStatusMail->idEmailTemplate))?$directStatusMail->idEmailTemplate:'0';
      if(Parameter::getUserParameter('notReceiveHisOwnEmails')=='YES' and ! $cronnedScript){
        $tabDest=explode(",", $destTab[$idTemplate]);
        if(count($tabDest)!=count($resultMail)){
          $curUser=new Affectable(getSessionUser()->id);
          foreach ($tabDest as $id=>$mail){
            if(trim($mail)==$curUser->email){
              unset($tabDest[$id]);
            }
          }
        }
        $destTab[$idTemplate]=implode(",", $tabDest);
      }
      if ($resultMail and $resultMail[0]!='') {
        return array('result' => 'OK', 'dest' => $destTab[$idTemplate]);
      }else if ($resultMail[0]=='' and Parameter::getUserParameter('notReceiveHisOwnEmails')=='YES' and trim(Parameter::getUserParameter('paramMailSmtpServer'))!='' ){
        return array('result' => 'Fail', 'dest' => $destTab[$idTemplate]);
      }else{
        return array('result' => '', 'dest' => $destTab[$idTemplate]);
      }
    }
    
    $valide=0;
    if ($resultMail and is_array($resultMail)) {
      foreach ($resultMail as $id=>$value){
        if($value!=''){
          $valide++;
        }
      }
    }
    if($valide!=0){
      return $resultMail;
    }
    return;
  }

  public static function getBaseUrl() {
    if (isset ( $_SERVER ['REQUEST_URI'] )) {
      $uri=$_SERVER ['REQUEST_URI'];
    } else { // FIX FOR IIS
      if (isset($_SERVER ['PHP_SELF'])) {
        $uri = substr ( $_SERVER ['PHP_SELF'], 1 );
        if (isset ( $_SERVER ['QUERY_STRING'] )) {
          $uri .= '?' . $_SERVER ['QUERY_STRING'];
        }
      } else {
        $uri='/view/main.php';
      }
    }
    $port=(isset($_SERVER['SERVER_PORT']))?$_SERVER ['SERVER_PORT']:'80';
    $https=(isset($_SERVER['HTTPS']))?$_SERVER ['HTTPS']:'off';
    $serverName=(isset($_SERVER['SERVER_NAME']))?$_SERVER['SERVER_NAME']:'';
    if ( (!$serverName or strlen($serverName)<=3) and isset($_SERVER['SERVER_ADDR'])) $serverName=$_SERVER['SERVER_ADDR'];
    $url = ( ($https and strtolower($https)=='on') or $port=='443')?'https://':'http://'; 
    $url.= $serverName;
    $url.= ($port!='80' and $port!='443')?':'.$port:'';
    $url.= $uri;
    $ref = "";
    if (strpos ( $url, '/view/' )) { // Attention, to give correct url for csv export, view must be first
      $ref .= substr ( $url, 0, strpos ( $url, '/view/' ) );
    } else if (strpos ( $url, '/tool/' )) {
      $ref .= substr ( $url, 0, strpos ( $url, '/tool/' ) );
    } else  if (strpos ( $url, '/report/' )) {
      $ref .= substr ( $url, 0, strpos ( $url, '/report/' ) );
    } else if (strpos ( $url, '/sso/projeqtor/' )) {
      $ref .= substr ( $url, 0, strpos ( $url, '/sso/projeqtor/' ) );
    } else if (strpos ( $url, '/sso/' )) {
      $ref .= substr ( $url, 0, strpos ( $url, '/sso/' ) );
    } else if (strpos ( $url, '/mobile/' )) {
      $ref .= substr ( $url, 0, strpos ( $url, '/mobile/' ) );
    }
    return $ref;
  }

  public function parseMailMessage($message) {
    $arrayFrom = array();
    $arrayTo = array();
    $objectClass = get_class($this);
    if ($objectClass == 'TicketSimple') { $objectClass = 'Ticket'; }
    $item = i18n ( $objectClass );
    if ($objectClass == 'Project') {
      $project = $this;
    } else if (property_exists ( $this, 'idProject' )) {
      $project = new Project ( $this->idProject );
    } else {
      $project = new Project ();
    }
    
    // db display name
    $arrayFrom [] = '${dbName}';
    $arrayTo [] = Parameter::getGlobalParameter ( 'paramDbDisplayName' );
    
    // Class of item
    $arrayFrom [] = '${item}';
    $arrayTo [] = $item;
    
    // id
    $arrayFrom [] = '${id}';
    $arrayTo [] = $this->id;
    
    // name
    $arrayFrom [] = '${name}';
    $arrayTo [] = (property_exists ( $this, 'name' )) ? $this->name : '';
    
    // status
    $arrayFrom [] = '${status}';
    $arrayTo [] = (property_exists ( $this, 'idStatus' )) ? SqlList::getNameFromId ( 'Status', $this->idStatus ) : '';
    
    // project
    $arrayFrom [] = '${project}';
    $arrayTo [] = $project->name;
    
    // type
    $typeName = 'id' . $objectClass . 'Type';
    $arrayFrom [] = '${type}';
    $arrayTo [] = (property_exists ( $this, $typeName )) ? SqlList::getNameFromId ( $objectClass . 'Type', $this->$typeName ) : '';
    
    // reference
    $arrayFrom [] = '${reference}';
    $arrayTo [] = (property_exists ( $this, 'reference' )) ? $this->reference : '';
    
    // externalReference
    $arrayFrom [] = '${externalReference}';
    $arrayTo [] = (property_exists ( $this, 'externalReference' )) ? $this->externalReference : '';
    
    // issuer
    $arrayFrom [] = '${issuer}';
    $arrayTo [] = (property_exists ( $this, 'idUser' )) ? SqlList::getNameFromId ( 'User', $this->idUser ) : '';
    
    // responsible
    $arrayFrom [] = '${responsible}';
    $arrayTo [] = (property_exists ( $this, 'idResource' )) ? SqlList::getNameFromId ( 'Resource', $this->idResource ) : '';
    
    // sender
    $arrayFrom [] = '${sender}';
    $user = getSessionUser ();
    $arrayTo [] = ($user->resourceName) ? $user->resourceName : $user->name;
    
    // context1 to context3
    $arrayFrom [] = '${context1}';
    $arrayFrom [] = '${context2}';
    $arrayFrom [] = '${context3}';
    $arrayTo [] = (property_exists ( $this, 'idContext1' )) ? SqlList::getNameFromId ( 'Context', $this->idContext1 ) : '';
    $arrayTo [] = (property_exists ( $this, 'idContext2' )) ? SqlList::getNameFromId ( 'Context', $this->idContext2 ) : '';
    $arrayTo [] = (property_exists ( $this, 'idContext3' )) ? SqlList::getNameFromId ( 'Context', $this->idContext3 ) : '';
    
    // sponsor
    $arrayFrom [] = '${sponsor}';
    $arrayTo [] = SqlList::getNameFromId ( 'Sponsor', $project->idSponsor );
    
    // projectCode
    $arrayFrom [] = '${projectCode}';
    $arrayTo [] = $project->projectCode;
    
    // ContractCode
    $arrayFrom [] = '${contractCode}';
    $arrayTo [] = $project->contractCode;
    
    // Customer
    $arrayFrom [] = '${customer}';
    $arrayTo [] = SqlList::getNameFromId ( 'Client', $project->idClient );
    
    // url (direct access to item)
    $arrayFrom [] = '${url}';
    if ($objectClass == 'User') {
      // FIX FOR IIS
      $arrayTo [] = self::getBaseUrl ();
    } else {
      $arrayTo [] = $this->getReferenceUrl ();
    }
    
    // login
    $arrayFrom [] = '${login}';
    $arrayTo [] = ($objectClass == 'User') ? $this->name : getSessionUser ()->name;
    
    // password
    $arrayFrom [] = '${password}';
    $arrayTo [] = ($objectClass=='User')?(($this->crypto==null)?$this->password:'('.i18n("passwordAlreadyChanged").')'):'';
    
    // admin mail
    $arrayFrom [] = '${adminMail}';
    $arrayTo [] = Parameter::getGlobalParameter ( 'paramAdminMail' );
    
    // Format title
    return str_replace ( $arrayFrom, $arrayTo, $message );
  }

  /**
   * Get the detail of object, to be send by mail
   * This is a simplified copy of objectDetail.php, in print mode
   */
  public function getMailDetail($directMessage=null) {
    $currencyPosition = Parameter::getGlobalParameter ( 'currencyPosition' );
    $currency = Parameter::getGlobalParameter ( 'currency' );
    SqlList::cleanAllLists (); // To be sure...
    $msg = "";
    $rowStart = '<tr>';
    $rowEnd = '</tr>';
    $labelStart = '<td style="background:#FFFFFF;font-weight:bold;text-align: right;width:30%;min-width:30%;vertical-align: middle;white-space:nowrap;padding-bottom:10px;">&nbsp;&nbsp;';
    $labelLinkStart = '<td style="background:#FFFFFF;text-align: right;width:30%;min-width:30%;vertical-align: top;white-space:nowrap;padding-bottom:10px;">&nbsp;&nbsp;';
    $labelEnd = '&nbsp;</td>';
    $fieldStart = '<td style="background:#FFFFFF;text-align: left;width:70%;padding-left:20px;padding-bottom:10px;vertical-align: top;">';//<td style="width:2px;">&nbsp;</td>
    $fieldEnd = '</td>';
    $sectionStart = '<td style="background:#606062;color: #FFFFFF; text-align: center;font-size:14pt;font-weight:normal;width: 100%;font-family:Verdana,Arial,Helvetica,sans-serif" colspan="2">';
    $sectionEnd = '</td>';
    $tableStart = '<table style="font-size:11pt; width: 100%;font-family: Verdana, Arial, Helvetica, sans-serif;">';
    $tableEnd = '</table>';
    //florent
    $ref = $this->getReferenceUrl ();
    $replyMail=i18n("replyToMail");
    $firstLine = " 
        <table style='font-size:14pt;min-width: 800px; max-width: 1200px; width:95%;font-family:Verdana,Arial,Helvetica,sans-serif;padding-bottom:10px;'><tr style='height:22px;'>
        <td colspan='2' style='background:#606062;color: #FFFFFF; text-align: center;vertical-align: middle;'>
        <div style='background:#F0F0F0;color:#A0A0A0;font-style:italic;font-size:80%'>".htmlEncode ( $replyMail)."</div><div style='background:#606062;color:#606062;font-size:1pt;'>###PROJEQTOR###</div>
        <div style='vertical-align: middle;'>
        <img style='width:22px; height:22px;-webkit-filter :brightness(0) invert(1);filter: brightness(0) invert(1);' src='".self::getBaseUrl()."/view/css/customIcons/grey/icon".get_class ( $this ).".png'/>
        &nbsp;
        <a href='". $ref ."' target='#' style='color:white'>". i18n ( get_class ( $this ) ) ." #". htmlEncode ( $this->id ) ."</a></div></td>
        </tr>";
    $dmsg=(isset($directMessage))?$this->parseMailMessage ($directMessage):'';
    if(Parameter::getGlobalParameter('cronCheckEmailsHost')!='' and Parameter::getGlobalParameter('cronCheckEmails')>0){
      $msg = " \n ".$firstLine."\n".$dmsg."\n";
    }else{
      $msg = "<table style='font-size:14pt;min-width: 800px; max-width: 1200px; width:95%;font-family:Verdana,Arial,Helvetica,sans-serif;'><tr style='height:22px;'>";
      $msg .= "  <td colspan='2' style='background:#606062;color: #FFFFFF; text-align: center;vertical-align: middle;'><div style='vertical-align: middle;'>";
      $msg .= "  <img style='width:22px; height:22px;-webkit-filter :brightness(0) invert(1);filter: brightness(0) invert(1);' src='".self::getBaseUrl()."/view/css/customIcons/grey/icon".get_class ( $this ).".png'/>";
      $msg .= '  &nbsp;';
      $msg .= "  <a href='". $ref ."' target='#' style='color:white'>". i18n ( get_class ( $this ) ) ." #". htmlEncode ( $this->id ) ."</a></div></td>";
      $msg .= "  </tr>";
      $msg .= " \n ".$dmsg."\n";
    }
    $msg .= '<tr><td style="padding-bottom:10px"></td></tr>';
    $msg .= '<tr>';
    $msg .= ' <td style="text-align:center;font-size:14pt;color:#606062;background:#DDDDDD;border:1px solid #606062;vertical-align: middle;" colspan="2">'.SqlList::getNameFromId(get_class ( $this ), $this->id).'</td>';
    $msg .= '</tr>';
    $msg .= '<tr><td style="padding-bottom:10px"></td></tr>';
    //
    $colArray = array();
    $section = null;
    $nobr = false;
    foreach ( $this as $col => $val ) {
      $hide = false;
      $nobr_before = $nobr;
      $nobr = false;
      if (substr ( $col, 0, 4 ) == '_tab') {
        // Nothing
      } else if (substr ( $col, 0, 5 ) == '_sec_') {
        if (strlen ( $col ) > 8) {
          $section = substr ( $col, 5 );
          $section = ucfirst ( $section );
          if ($section == 'Description' or $section == 'Treatment') {
            $colArray[$section] = array($col=>$section);
          }
        } else {
          $section = '';
        }
      } else if (substr ( $col, 0, 5 ) == '_spe_') {
        // Nothing
      } else if (substr ( $col, 0, 6 ) == '_calc_') {
        $item = substr ( $col, 6 );
        $colArray[$section][$col]=$item;
      } else if (substr ( $col, 0, 5 ) == '_lib_') {
        $item = substr ( $col, 5 );
        if (strpos ( $this->getFieldAttributes ( $col ), 'nobr' ) !== false) {
          $nobr = true;
        }
        $colArray[$section][$col]=$item;
      } else if (substr ( $col, 0, 5 ) == '_Link') {
        // Nothing
      } else if (substr ( $col, 0, 11 ) == '_Assignment') {
        // Nothing
      } else if (substr ( $col, 0, 11 ) == '_Approver') {
        // Nothing
      } else if (substr ( $col, 0, 15 ) == '_VersionProject') {
        // Nothing
      } else if (substr ( $col, 0, 11 ) == '_Dependency') {
        // Nothing
      } else if ($col == '_ResourceCost') {
        // Nothing
      } else if ($col == '_DocumentVersion') {
        // Nothing
      } else if ($col == '_ExpenseDetail') {
        // Nothing
      } else if (substr ( $col, 0, 12 ) == '_TestCaseRun') {
        // Nothing
      } else if (substr ( $col, 0, 1 ) == '_' and substr ( $col, 0, 6 ) != '_void_' and substr ( $col, 0, 7 ) != '_label_') {
        // Nothing
      } else {
        $dataType = $this->getDataType ( $col );
        $dataLength = $this->getDataLength ( $col );
        if ($dataType == 'decimal' and substr ( $col, - 4, 4 ) == 'Work') {
          $hide = true;
        }
        if (strpos ( $this->getFieldAttributes ( $col ), 'hidden' ) !== false) {
          $hide = true;
        }
        if (is_object ( $val )) {
          if (get_class ( $val ) == 'Origin') {
            if ($val->originType and $val->originId) {
              $val = i18n ( $val->originType ) . ' #' . htmlEncode ( $val->originId ) . ' : ' . htmlEncode ( SqlList::getNameFromId ( $val->originType, $val->originId ) );
            } else {
              $val = "";
            }
            $dataType = 'varchar';
            $dataLength = 4000;
          } else {
            $hide = true;
          }
        }
        if ($hide) {
          continue;
        }
        $colArray[$section][$col]=$val;
      }
    }
    $msg .= $rowStart.'<td style="width:50%;vertical-align:top;padding-right:15px;min-width:500px;">';
    if(isset($colArray['Description']))self::drawMailDetailCol($colArray['Description'], $msg);
    if(isset($colArray['Treatment']))self::drawMailDetailCol($colArray['Treatment'], $msg);
    $msg .= $fieldEnd.'<td style="width:50%;vertical-align:top;padding-left:15px;">';
    if (isset ( $this->_Link ) and is_array ( $this->_Link )) {
    	$msg .= $tableStart;
    	$msg .= $rowStart . $sectionStart.'<table style="float:left;"><tr>';
    	$msg .= '<td>&nbsp;</td>';
    	$msg .= '<td><img style="float:left;width:22px; height:22px;" src="'.SqlElement::getBaseUrl().'/view/css/customIcons/new/iconEmailLinkedElement.png" /></td>';
    	$msg .= '<td>&nbsp;</td>';
    	$msg .= '<td style="color: #FFFFFF;font-size:14pt;font-weight:normal;white-space:nowrap;font-family:Verdana,Arial,Helvetica,sans-serif">'.i18n ( 'sectionLink' ).'</td>';
    	$msg .= '<td style="width:90%;">&nbsp;</td>';
    	$msg .= '</tr></table>'.$sectionEnd.$rowEnd;
    	$msg .= $rowStart.'<td><br></td>'.$rowEnd;
    	$links=$this->_Link;
    	if (count($links)==0) {//
    	  $link=new Link();
    	  $className=get_class($this);
    	  $where="(ref1Type='$className' and ref1Id=$this->id) or (ref2Type='$className' and ref2Id=$this->id)";
    	  $links=$link->getSqlElementsFromCriteria(null,null,$where);
    	}
    	foreach ( $links as $link ) {
    		if($link->ref1Id == $this->id and $link->ref1Type == get_class($this)){
    			$refLinkType = $link->ref2Type;
    			$refLinkId = $link->ref2Id;
    		} else if ($link->ref2Id == $this->id and $link->ref2Type == get_class($this)) {
    			$refLinkType = $link->ref1Type;
    			$refLinkId = $link->ref1Id;
    		}
    		$creationDate = $link->creationDate;
    		$msg .= $rowStart . $labelLinkStart;
    		$userId = $link->idUser;
    		$userName = SqlList::getNameFromId ( 'User', $userId );
    		$msg .= '<b>'.$userName.'&nbsp;&nbsp;</b>';
    		$msg .= '<br>'.htmlFormatDateTime ( $creationDate ,false, false, false);
    		$msg .= $labelEnd . $fieldStart;
    		$msg .= '<b>'.i18n($refLinkType);
    		$msg .= '&nbsp;#'.$refLinkId.'</b>&nbsp;-&nbsp;';
    		$nameLink = SqlList::getNameFromId($refLinkType,$refLinkId);
    		$msg.=htmlEncode($nameLink,'print');
    		$msg .= $fieldEnd . $rowEnd;
    	}
    	$msg .= $tableEnd;
    }
    if (isset ( $this->_Note ) and is_array ( $this->_Note )) {
    	//florent
    	$msg .= $tableStart;
    	$msg=$this->getNotesClassicTab($msg, $rowStart,$rowEnd, $sectionStart, $sectionEnd,$labelLinkStart, $labelEnd,$fieldStart,$fieldEnd);
    	$msg .= $tableEnd;
    }
    $msg .= $fieldEnd.$rowEnd;
    $msg .= $rowStart.'<td style="width:50%;vertical-align:top;">';
    // ADDITION BY papjul - Document Version details
    if (isset ( $this->_DocumentVersion ) and is_array ( $this->_DocumentVersion )) {
      $msg .= $tableStart;
      $msg .= $rowStart . $sectionStart.'<table style="float:left;"><tr>';
      $msg .= '<td>&nbsp;</td>';
      $msg .= '<td><img style="width:22px; height:22px;" src="'.SqlElement::getBaseUrl().'/view/css/customIcons/new/iconEmailAttachment.png" /></td>';
      $msg .= '<td>&nbsp;</td>';
      $msg .= '<td style="color: #FFFFFF;font-size:14pt;font-weight:normal;white-space:nowrap;font-family:Verdana,Arial,Helvetica,sans-serif">'.i18n ( 'sectionDocumentVersion' ).'</td>';
      $msg .= '<td style="width:90%;">&nbsp;</td>';
      $msg .= '</tr></table>'.$sectionEnd.$rowEnd;
      $documentVersion = new DocumentVersion ();
      $documentVersions = $documentVersion->getSqlElementsFromCriteria ( array('idDocument' => $this->id), false, null, 'id desc' );
      foreach ( $documentVersions as $documentVersion ) {
        $name = $documentVersion->name;
        $versionDate = $documentVersion->versionDate;
        $msg .= $rowStart . $labelStart;
        $msg .= $name;
        $msg .= '<br />';
        $msg .= htmlFormatDateTime ( $versionDate ,false, false, false);
        $msg .= $labelEnd . $fieldStart;
        $msg .= $documentVersion->fileName;
        $msg .= '<br />';
        $text = new Html2Text ( $documentVersion->description );
        $plainText = $text->getText ();
        if (mb_strlen ( $plainText ) > 10000) { // Should not send too long email
          $descriptionTruncated = nl2br ( mb_substr ( $plainText, 0, 10000 ) );
          $msg .= $descriptionTruncated;
        } else {
          $msg .= $documentVersion->description;
        }
        $msg .= $fieldEnd . $rowEnd;
      }
      $msg .= $tableEnd;
    }
    // End of ADDITION BY papjul - Document Version details
    $msg .= $fieldEnd.$rowEnd.$tableEnd;
    return $msg;
  }
  
public function drawMailDetailCol($colArray, &$msg){
  $currencyPosition = Parameter::getGlobalParameter ( 'currencyPosition' );
  $currency = Parameter::getGlobalParameter ( 'currency' );
  $rowStart = '<tr>';
  $rowEnd = '</tr>';
  $labelStart = '<td style="background:#FFFFFF;font-weight:bold;text-align: right;width:30%;min-width:30%;vertical-align: top;white-space:nowrap;padding-bottom:10px;">&nbsp;&nbsp;';
  $labelEnd = '&nbsp;</td>';
  $fieldStart = '<td style="background:#FFFFFF;text-align: left;width:70%;padding-left:20px;padding-bottom:10px;vertical-align: top;">';//<td style="width:2px;">&nbsp;</td>
  $fieldEnd = '</td>';
  $sectionStart = '<td style="background:#606062;color: #FFFFFF; text-align: center;font-size:14pt;font-weight:normal;width: 100%;font-family:Verdana,Arial,Helvetica,sans-serif" colspan="2">';
  $sectionEnd = '</td>';
  $tableStart = '<table style="font-size:11pt; width: 100%;font-family: Verdana, Arial, Helvetica, sans-serif;">';
  $tableEnd = '</table>';
  
  $section = null;
  $nobr = false;
  foreach ( $colArray as $col => $val ) {
  	$hide = false;
  	$nobr_before = $nobr;
  	$nobr = false;
  	if (substr ( $col, 0, 4 ) == '_tab') {
  		// Nothing
  	} else if (substr ( $col, 0, 5 ) == '_sec_') {
  		if (strlen ( $col ) > 8) {
  			$section = substr ( $col, 5 );
  			$section = ucfirst ( $section );
  			if ($section == 'Description' or $section == 'Treatment') {
  				$msg .= $tableStart;
  				$msg .= $rowStart . $sectionStart.'<table><tr>';
  				$msg .= '<td>&nbsp;</td>';
  				$msg .= '<td><img style="float:left;width:22px; height:22px;" src="'.SqlElement::getBaseUrl().'/view/css/customIcons/new/iconEmail'.$section.'.png" /></td>';
  				$msg .= '<td>&nbsp;</td>';
  				$msg .= '<td style="color: #FFFFFF;font-size:14pt;font-weight:normal;white-space:nowrap;font-family:Verdana,Arial,Helvetica,sans-serif">'.i18n ( 'section' . $section ).'</td>';
  				$msg .= '<td style="width:90%;">&nbsp;</td>';
  				$msg .= '</tr></table>'.$sectionEnd.$rowEnd;
  				$msg .= $rowStart.'<td><br></td>'.$rowEnd;
  			}
  		} else {
  			$section = '';
  		}
  	} else if (substr ( $col, 0, 5 ) == '_spe_') {
  		// Nothing
  	} else if (substr ( $col, 0, 6 ) == '_calc_') {
  		$item = substr ( $col, 6 );
  		$msg .= $this->drawCalculatedItem ( $item );
  	} else if (substr ( $col, 0, 5 ) == '_lib_') {
  		$item = substr ( $col, 5 );
  		if (strpos ( $this->getFieldAttributes ( $col ), 'nobr' ) !== false) {
  			$nobr = true;
  		}
  		if ($this->getFieldAttributes ( $col ) != 'hidden') {
  			$msg .= (($nobr) ? '&nbsp;' : '') . i18n ( $item ) . '&nbsp;';
  		}
  		if (! $nobr) {
  			$msg .= $fieldEnd . $rowEnd;
  		}
  	} else if (substr ( $col, 0, 5 ) == '_Link') {
  		// Nothing
  	} else if (substr ( $col, 0, 11 ) == '_Assignment') {
  		// Nothing
  	} else if (substr ( $col, 0, 11 ) == '_Approver') {
  		// Nothing
  	} else if (substr ( $col, 0, 15 ) == '_VersionProject') {
  		// Nothing
  	} else if (substr ( $col, 0, 11 ) == '_Dependency') {
  		// Nothing
  	} else if ($col == '_ResourceCost') {
  		// Nothing
  	} else if ($col == '_DocumentVersion') {
  		// Nothing
  	} else if ($col == '_ExpenseDetail') {
  		// Nothing
  	} else if (substr ( $col, 0, 12 ) == '_TestCaseRun') {
  		// Nothing
  	} else if (substr ( $col, 0, 1 ) == '_' and substr ( $col, 0, 6 ) != '_void_' and substr ( $col, 0, 7 ) != '_label_') {
  		// Nothing
  	} else {
  		$attributes = '';
  		$isRequired = false;
  		$readOnly = false;
  		$specificStyle = '';
  		$dataType = $this->getDataType ( $col );
  		$dataLength = $this->getDataLength ( $col );
  		if ($dataType == 'decimal' and substr ( $col, - 4, 4 ) == 'Work') {
  			$hide = true;
  		}
  		if (strpos ( $this->getFieldAttributes ( $col ), 'hidden' ) !== false) {
  			$hide = true;
  		}
  		if (strpos ( $this->getFieldAttributes ( $col ), 'nobr' ) !== false) {
  			$nobr = true;
  		}
  		if (strpos ( $this->getFieldAttributes ( $col ), 'invisible' ) !== false) {
  			$specificStyle .= ' visibility:hidden';
  		}
  		if (is_object ( $val )) {
  			if (get_class ( $val ) == 'Origin') {
  				if ($val->originType and $val->originId) {
  					$val = i18n ( $val->originType ) . ' #' . htmlEncode ( $val->originId ) . ' : ' . htmlEncode ( SqlList::getNameFromId ( $val->originType, $val->originId ) );
  				} else {
  					$val = "";
  				}
  				$dataType = 'varchar';
  				$dataLength = 4000;
  			} else {
  				$hide = true;
  			}
  		}
  		if ($hide) {
  			continue;
  		}
  		if (! $nobr_before) {
  			$msg .= $rowStart . $labelStart . $this->getColCaption ( $col ) . $labelEnd . $fieldStart;
  		} else {
  			$msg .= "&nbsp;&nbsp;&nbsp;";
  		}
  		if (is_array ( $val )) {
  			// Nothing
  		} else if (substr ( $col, 0, 6 ) == '_void_') {
  			// Nothing
  		} else if (substr ( $col, 0, 7 ) == '_label_') {
  			// $captionName=substr($col,7);
  			// $msg.='<label class="label shortlabel">' . i18n('col' . ucfirst($captionName)) . '&nbsp;:&nbsp;</label>';
  		} else if ($hide) {
  			// Nothing
  		} else if ($dataLength > 4000) {
  			if (mb_strlen ( $val ) > 1000000) {
  				$text = new Html2Text ( $val );
  				$plainText = $text->getText();
  				$msg .= htmlSetClickableImages(nl2br(mb_substr($plainText,0, 1000000)), 450);
  			} else {
  				$msg .= htmlSetClickableImages($val, 450);
  			}
  		} else if (strpos ( $this->getFieldAttributes ( $col ), 'displayHtml' ) !== false) {
  			$msg .= $val;
  		} else if ($col == 'id') { // id
  			$msg .= '<span style="color:grey;">#</span>' . $val;
  		} else if ($col == 'password') {
  			$msg .= "*****"; // nothing
  		} else if ($dataType == 'date' and $val != null and $val != '') {
  			$msg .= htmlFormatDate ( $val ,false, false);
  		} else if ($dataType == 'datetime' and $val != null and $val != '') {
  			$msg .= htmlFormatDateTime ( $val, false, false, false);
  		} else if ($dataType == 'time' and $val != null and $val != '') {
  			$msg .= htmlFormatTime ( $val, false );
  		} else if ($col == 'color' and $dataLength == 7) { // color
  			//nothing
  		} else if ($dataType == 'int' and $dataLength == 1) { // boolean
  			$msg .= '<input type="checkbox" disabled="disabled" ';
  			if ($val != '0' and ! $val == null) {
  				$msg .= ' checked />';
  			} else {
  				$msg .= ' />';
  			}
  			// BEGIN - REPLACE BY TABARY - USE isForeignKey GENERIC FUNCTION
  		} else if (isForeignKey( $col, $this)) { // Idxxx
  			//        } else if (substr ( $col, 0, 2 ) == 'id' and $dataType == 'int' and strlen ( $col ) > 2 and substr ( $col, 2, 1 ) == strtoupper ( substr ( $col, 2, 1 ) )) { // Idxxx
  			// END - REPLACE BY TABARY - USE isForeignKey GENERIC FUNCTION
  			// BEGIN -  ADD BY TABARY - POSSIBILITY TO HAVE X TIMES IDXXXX IN SAME OBJECT
  			$col_withoutAlias = foreignKeyWithoutAlias($col);
  			$msg .= htmlEncode ( SqlList::getNameFromId ( substr($col_withoutAlias,2), $val ), 'print' );
  			// END -  ADD BY TABARY - POSSIBILITY TO HAVE X TIMES IDXXXX IN SAME OBJECT
  		} else if (substr ( $col, 0, 2 ) == 'id' and $dataType == 'int' and strlen ( $col ) > 2 and substr ( $col, 2, 1 ) == strtoupper ( substr ( $col, 2, 1 ) )) { // Idxxx
  			$msg .= htmlEncode ( SqlList::getNameFromId ( substr ( $col, 2 ), $val ), 'print' );
  		} else if ($dataLength > 100) { // Text Area (must reproduce BR, spaces, ...
  			$msg .= htmlEncode ( $val, 'print' );
  		} else if ($dataType == 'decimal' and (substr ( $col, - 4, 4 ) == 'Cost' or substr ( $col, - 6, 6 ) == 'Amount' or $col == 'amount')) {
  			if ($currencyPosition == 'after') {
  				$msg .= htmlEncode ( $val, 'print' ) . ' ' . $currency;
  			} else {
  				$msg .= $currency . ' ' . htmlEncode ( $val, 'print' );
  			}
  		} else if ($dataType == 'decimal' and substr ( $col, - 4, 4 ) == 'Work') {
  			// $msg.= Work::displayWork($val) . ' ' . Work::displayShortWorkUnit();
  		} else {
  			if ($this->isFieldTranslatable ( $col )) {
  				$val = i18n ( $val );
  			}
  			if (strpos ( $this->getFieldAttributes ( $col ), 'html' ) !== false) {
  				$msg .= $val;
  			} else {
  				$msg .= htmlEncode ( $val, 'print' );
  			}
  		}
  		if (! $nobr) {
  			$msg .= $rowEnd;
  		}
  	}
  }
  $msg .= $tableEnd;
}

/** ========================================================================
 * Return the HTML last changes history table of an object.
 * It is a copy of drawHistoryFromObjects();
 * @return string
 */
public function getLastChangeTabForObject($obj,$lastChangeDate) {
  global $cr, $print, $treatedObjects;
  if ($lastChangeDate=='full') {
    $lastChangeToShow='1970-01-01 00:00:00';
  } else {
    if (!$lastChangeDate) {
      $lastChangeDate=date('Y-m-d H:i:s');
    }
    $lastChangeToShow=date('Y-m-d H:i:s',strtotime($lastChangeDate)-10); // Get last changes (including last 10 seconds, not only last change)
  }
  require_once "../tool/formatter.php";
  if ($obj->id) {
    $inList="( ('" . get_class($obj) . "', " . Sql::fmtId($obj->id) . ") )";
  } else {
    $inList="( ('x',0) )";
  }
  $showWorkHistory=true;
  $where=' (refType, refId) in ' . $inList;
  $order=' operationDate desc, id asc';
  $hist=new History();
  $historyList=$hist->getSqlElementsFromCriteria(null, false, $where, $order, false, false);
  $style = 'border-top: 1px solid #7b7b7b ; border-bottom: 1px solid #7b7b7b;
            background-color:#dddddd; padding:4px;';
  $historyTabHtml =  '<table style="width:95%; border-collapse:collapse; border:1px solid #7b7b7b;">';

  $historyTabHtml .=  '<tr><td style="'. $style . 'text-align:center;"colspan="6">'.i18n('elementHistory'.(($lastChangeDate=='full')?'':'Last')).'</td></tr>' .//
                      '<tr><td style="' . $style . '" width="10%">' . i18n('colOperation') . '</td>
                      <td style="' . $style . '" width="14%">' . i18n('colColumn') . '</td>
                      <td style="' . $style . '" width="23%">' . i18n('colValueBefore') . '</td>
                      <td style="' . $style . '" width="23%">' . i18n('colValueAfter') . '</td>
                      <td style="' . $style . '" width="15%">' . i18n('colDate') . '</td>
                      <td style="' . $style . '" width="15%">' . i18n('colUser') . '</td>
                      </tr>';   
  $stockDate=null;               
  $stockUser=null;
  $stockOper=null;
  if (is_array($historyList) and count($historyList)>0 and is_object($historyList[0]))
    $dateCmp = new DateTime($historyList[0]->operationDate);
  else
    return $historyTabHtml . '</table>';
  foreach ( $historyList as $hist ) {
    if ($hist->operationDate<$lastChangeToShow) break;
    if (substr($hist->colName, 0, 24) == 'subDirectory|Attachment|'  or substr($hist->colName, 0, 18) == 'idTeam|Attachment|'
     or substr($hist->colName, 0, 25) == 'subDirectory|Attachement|' or substr($hist->colName, 0, 19) == 'idTeam|Attachement|') {
      continue;
    }
    $colName=($hist->colName == null)?'':$hist->colName;
    $split=explode('|', $colName);
    if (count($split) == 3) {
      $colName=$split [0];
      $refType=$split [1];
      $refId=$split [2];
      $refObject='';
    } else if (count($split) == 4) {
      $refObject=$split [0];
      $colName=$split [1];
      $refType=$split [2];
      $refId=$split [3];
    } else {
      $refType='';
      $refId='';
      $refObject='';
    }
    if ($refType=='Attachement') {
      $refType='Attachment'; // New in V5 : change Class name, must preserve display for history
    }
    $curObj=null;
    $dataType="";
    $dataLength=0;
    $hide=false;
    $oper=i18n('operation' . ucfirst($hist->operation));
    $user=$hist->idUser;
    $user=SqlList::getNameFromId('User', $user);
    $date=htmlFormatDateTime($hist->operationDate);
    $class="NewOperation";
    if ($stockDate == $hist->operationDate and $stockUser == $hist->idUser and $stockOper == $hist->operation) {
      $oper="";
      $user="";
      $date="";
      $class="ContinueOperation";
    }
    if ($colName != '' or $refType != "") {
      if ($refType) {
        if ($refType == "TestCase") {
          $curObj=new TestCaseRun();
        } else {
          $curObj=new $refType();
        }
      } else {
        $curObj=new $hist->refType();
      }
      if ($curObj) {
        if ($refType) {
          $colCaption=i18n($refType) . ' #' . $refId . ' ' . $curObj->getColCaption($colName);
          if ($refObject) {
            $colCaption=i18n($refObject) . ' - ' . $colCaption;
          }
        } else {
          $colCaption=$curObj->getColCaption($colName);
        }
        $dataType=$curObj->getDataType($colName);
        $dataLength=$curObj->getDataLength($colName);
        if (strpos($curObj->getFieldAttributes($colName), 'hidden') !== false) {
          $hide=true;
        }
      }
    } else {
      $colCaption='';
    }
    if (substr($hist->refType, -15) == 'PlanningElement' and $hist->operation == 'insert') {
      $hide=true;
    }
    if ($hist->isWorkHistory and ! $showWorkHistory) {
      $hide=true;
    }
    if (!$hide) {
      $historyTabHtml .=  '<tr>';
      $historyTabHtml .=  '<td class="historyData' . $class .
                          '" style=" padding:4px; width:10%; border: 1px solid #7b7b7b;">' .
                          $oper . '</td>';
      $historyTabHtml .=  '<td class="historyData" style=" padding:4px; width:14%; border: 1px solid #7b7b7b;">' .
                          $colCaption . '</td>';
      $oldValue=$hist->oldValue;
      $newValue=$hist->newValue;
      if ($dataType == 'int' and $dataLength == 1) { // boolean
        $oldValue=htmlDisplayCheckbox($oldValue,true);
        $newValue=htmlDisplayCheckbox($newValue,true);
      } else if (substr($colName, 0, 2) == 'id' and strlen($colName) > 2 and strtoupper(substr($colName, 2, 1)) == substr($colName, 2, 1)) {
        if ($oldValue != null and $oldValue != '') {
          if ($oldValue == 0 and $colName == 'idStatus') {
            $oldValue='';
          } else {
            $oldValue=SqlList::getNameFromId(substr($colName, 2), $oldValue);
          }
        }
        if ($newValue != null and $newValue != '') {
          $newValue=SqlList::getNameFromId(substr($colName, 2), $newValue);
        }
      } else if ($colName == "color") {
        $oldValue=htmlDisplayColoredFull("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $oldValue);
        $newValue=htmlDisplayColoredFull("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $newValue);
      } else if ($dataType == 'date') {
        $oldValue=htmlFormatDate($oldValue);
        $newValue=htmlFormatDate($newValue);
      } else if ($dataType == 'datetime') {
        $oldValue=htmlFormatDateTime($oldValue);
        $newValue=htmlFormatDateTime($newValue);
      } else if ($dataType == 'decimal' and substr($colName, -4, 4) == 'Work') {
        $oldValue=Work::displayWork($oldValue) . ' ' . Work::displayShortWorkUnit();
        $newValue=Work::displayWork($newValue) . ' ' . Work::displayShortWorkUnit();
      } else if ($dataType == 'decimal' and (substr($colName, -4, 4) == 'Cost' or strtolower(substr($colName,-6,6))=='amount')) {
        $oldValue=htmlDisplayCurrency($oldValue);
        $newValue=htmlDisplayCurrency($newValue);
      } else if (substr($colName, -8, 8) == 'Duration') {
        $oldValue=$oldValue . ' ' . i18n('shortDay');
        $newValue=$newValue . ' ' . i18n('shortDay');
      } else if (substr($colName, -8, 8) == 'Progress') {
        $oldValue=$oldValue . ' ' . i18n('colPct');
        $newValue=$newValue . ' ' . i18n('colPct');
      } else if ($colName=='password' or $colName=='apiKey') {
        $allstars="**********";
        if ($oldValue) $oldValue=substr($oldValue,0,5).$allstars.substr($oldValue,-5);
        if ($newValue) $newValue=substr($newValue,0,5).$allstars.substr($newValue,-5);
      } else {
        if (! isTextFieldHtmlFormatted($oldValue)) {
          $oldValue = htmlEncode($oldValue, 'print');
          $oldValue=wordwrap($oldValue, 30, '<wbr>', false);
        }
        if (! isTextFieldHtmlFormatted($newValue)) {
          $newValue = htmlEncode($newValue, 'print');        
          $newValue=wordwrap($newValue, 30, '<wbr>', false);
        }
      }
      $historyTabHtml .=  '<td class="historyData'.(($colName=="color")?' colorNameData" style="':'" style="padding:4px;').' width:23%; border: 1px solid #7b7b7b;">' .
                          $oldValue . '</td>';
      $historyTabHtml .=  '<td class="historyData'.(($colName=="color")?' colorNameData" style="':'" style="padding:4px;').' width:23%; border: 1px solid #7b7b7b;">' .
                          $newValue . '</td>';
      $historyTabHtml .=  '<td class="historyData' . $class . '" style="width:15%; border: 1px solid #7b7b7b;">';
      $historyTabHtml .=   $date . '</td>';
      $historyTabHtml .=  '<td class="historyData' . $class .
                          '" style=" padding:4px; width:15%; border-right: 1px solid #AAAAAA;" >';
      if ($user) {
        $historyTabHtml .=  formatUserThumb($hist->idUser, $user, null,'16','left').'&nbsp;';
      }
      $historyTabHtml .=  $user .
                          '</td>
                          </tr>';
      $stockDate=$hist->operationDate;
      $stockUser=$hist->idUser;
      $stockOper=$hist->operation;
    }
  }
  return $historyTabHtml . '</table>';
}

function getAssignmentHtmlTab(){
  $ass = new Assignment();
  $class=get_class($this);
  $id=$this->id;
  $crit = " refType='$class' and refId=$id ";
  $linkAss = $ass->getSqlElementsFromCriteria(null,null,$crit);
  $style = 'border-top: 1px solid #7b7b7b ; border-bottom: 1px solid #7b7b7b;
            background-color:#dddddd; padding:4px;';
  $html = '<table style="width:50%; border-collapse:collapse;border:1px solid #7b7b7b;">';
  $html .= '<tr> <td  style="text-align:center;' . $style . '">' . ucfirst(i18n('assignedResourceList')) . '</td> </tr>';
  foreach ($linkAss as $link) {
    $html .= '<tr><td style="width:70%;border: 1px solid #7b7b7b; padding:4px;">'. SqlList::getNameFromId('Affectable', $link->idResource). '</td></tr>';
  }
  return $html . '</table>';
}

function getLinksHtmlTab() {
  $link = new Link;
  $class=get_class($this);
  $id=$this->id;
  $crit = " (ref1Type='$class' and ref1Id=$id ) or (ref2Type='$class' and ref2Id=$id )";
  $linkList = $link->getSqlElementsFromCriteria(null,null,$crit);
  $style = 'border-top: 1px solid #7b7b7b ; border-bottom: 1px solid #7b7b7b;
            background-color:#dddddd; padding:4px;';
  $html = '<table style="width:95%; border-collapse:collapse;border:1px solid #7b7b7b;">
          <tr>' .
          '<td style="' . $style . '" width="12%"></td>
          <td  style="' . $style . ' text-align:center;" width="76%">Linked Items</td>
          <td  style="' . $style . '" width="12%"></td>
          </tr>';
  $html .= '<tr>' .
          '<td style="' . $style . '">' . ucfirst(i18n('colType')) . '</td>
          <td  style="' . $style . '">' . ucfirst(i18n('colName')) . '</td>
          <td  style="' . $style . '">' . ucfirst(i18n('Status')) . '</td>
          </tr>';
  
  $status = '';
  foreach ($linkList as $link) {
    if ($class==$link->ref1Type and $id==$link->ref1Id) { 
      $obj = new $link->ref2Type($link->ref2Id);
    } else { 
      $obj = new $link->ref1Type($link->ref1Id);
    }
    $goto = $obj->getReferenceUrl ();
    $html .= '<tr><td style="border: 1px solid #7b7b7b; padding:4px;"><a href="' . $goto . '">' .
              i18n(get_class($obj)) . ' #' . $obj->id . '</a></td>' .
              '<td style="border: 1px solid #7b7b7b; padding:4px;">' . $obj->name . '</td>';
    if (property_exists($obj, 'idStatus'))
//       $status = colorNameFormatter(SqlList::getNameFromId('Status', $obj->idStatus) . "#split#" .
//           SqlList::getFieldFromId('Status', $obj->idStatus, 'color'));
      $status=htmlDisplayColoredFull(SqlList::getNameFromId('Status', $obj->idStatus),SqlList::getFieldFromId('Status', $obj->idStatus, 'color'));
    $html .=  '<td style="border: 1px solid #7b7b7b; padding:0;margin:0">' . $status . '</td></tr>';
    $status = '';
  }
  return $html . '</table>';
}

//florent ticket 4790
function getNotesClassicTab($msg, $rowStart,$rowEnd, $sectionStart, $sectionEnd,$labelStart, $labelEnd,$fieldStart,$fieldEnd){
  $msg .= $rowStart . $sectionStart.'<table style="float:left;"><tr>';
  $msg .= '<td>&nbsp;</td>';
  $msg .= '<td><img style="float:left;width:22px; height:22px;" src="'.SqlElement::getBaseUrl().'/view/css/customIcons/new/iconEmailNotes.png" /></td>';
  $msg .= '<td>&nbsp;</td>';
  $msg .= '<td style="color: #FFFFFF;font-size:14pt;font-weight:normal;font-family:Verdana,Arial,Helvetica,sans-serif">'.i18n ( 'sectionNote' ).'</td>';
  $msg .= '<td style="width:90%;">&nbsp;</td>';
  $msg .= '</tr></table>'.$sectionEnd.$rowEnd;
  $msg .= $rowStart.'<td><br></td>'.$rowEnd;
  $note = new Note ();
  $notes = $note->getSqlElementsFromCriteria ( array('refType' => get_class ( $this ), 'refId' => $this->id), false, null, 'id desc' );
  foreach ( $notes as $note ) {
    if ($note->idPrivacy == 1) {
      $userId = $note->idUser;
      $userName = SqlList::getNameFromId ( 'User', $userId );
      $creationDate = $note->creationDate;
      $updateDate = $note->updateDate;
      if ($updateDate == null) {
        $updateDate = '';
      }
      $msg .= $rowStart . $labelStart;
      $msg .= '<b>'.$userName.'&nbsp;&nbsp;</b><br>';
      if ($updateDate) {
        $msg .= '<i>' . htmlFormatDateTime ( $updateDate ,false, false, false) . '</i>';
      } else {
        $msg .= htmlFormatDateTime ( $creationDate ,false, false, false);
      }
      $msg .= $labelEnd . $fieldStart;
      // $msg.=htmlEncode($note->note,'print');
      $text = new Html2Text ( $note->note );
      $plainText = $text->getText ();
      if (mb_strlen ( $plainText ) > 10000) { // Should not send too long email
        $noteTruncated = nl2br ( mb_substr ( $plainText, 0, 10000 ) );
        $msg .= htmlSetClickableImages($noteTruncated,450);
      } else {
        $msg .= htmlSetClickableImages($note->note,450);
      }
      $msg .= $fieldEnd . $rowEnd;
    }
  }
  return  $msg;
}

function getNotesHtmlTab() {
  $html = '';
  $note = new Note();
  $critArray = array('refType' => get_class($this), 'refId' => $this->id, 'idPrivacy'=>'1');
  $order=Parameter::getGlobalParameter("paramOrderNoteMail");
  $noteList = $note->getSqlElementsFromCriteria($critArray);
  if($order=='ASC'){
    arsort($noteList);
  }
  $style = 'border-top: 1px solid #7b7b7b ; border-bottom: 1px solid #7b7b7b;
            background-color:#dddddd; padding:4px;';
  $html = '<table style="width:95%; border-collapse:collapse;border:1px solid #7b7b7b;">
          <tr>' .
          '<td style="' . $style . '" width="12%"></td>
          <td  style="' . $style . ' text-align:center;" width="76%">Notes</td>
          <td  style="' . $style . '" width="12%"></td>
          </tr>';
  $html .= '<tr>' .
          '<td style="' . $style . '">' . ucfirst(i18n('colId')) . '</td>
          <td  style="' . $style . '">' . ucfirst(i18n('colName')) . '</td>
          <td  style="' . $style . '">' . ucfirst(i18n('colDate')) . '</td>
          </tr>';
  $status = '';
  foreach ($noteList as $note) {
    $html .= '<tr><td style="border: 1px solid #7b7b7b; padding:4px;">' .
            $note->id . '</td>' . '<td style="border: 1px solid #7b7b7b; padding:4px;">'
            //. wordwrap($note->note, 50, '<wbr>', false)
            . $note->note
            . '</td>';
    if (property_exists($note, 'updateDate') and $note->updateDate != '')
      $date = $note->updateDate;
    else if (property_exists($note, 'creationDate') and isset($note->creationDate))
      $date = $note->creationDate;
    $html .= '<td style="border: 1px solid #7b7b7b; padding:4px;">' . $date . '</td></tr>';
  }
  return $html . '</table>';
}

/*
 * Fill a mail or a title mail template (EmailTemplate class), 
 * with properties of the "mailable" object, by replacing "${property}" 
 * by the property and "${HISTORY} by a table representing the last changes
 * of the object. ${LINK} and ${NOTE} by a table displaying links and notes
 */
public function getMailDetailFromTemplate($templateToReplace, $lastChangeDate=null, $isTitle=false) {
  global $lastEmailChangeDate;
  $lastEmailChangeDate=$lastChangeDate;
  $templateToReplace = $this->parseMailMessage($templateToReplace);
  $ref = $this->getReferenceUrl ();
  $replyMail=i18n("replyToMail");
  if(Parameter::getGlobalParameter('cronCheckEmailsHost')!='' and Parameter::getGlobalParameter('cronCheckEmails')>0 and ! $isTitle){
    $templateToReplace="
          <table style='font-size:14pt; width: 95%;font-family: Verdana, Arial, Helvetica, sans-serif;'><tr><td style='background:#555555;color: #FFFFFF; text-align: center;'>
          <div style='background:#F0F0F0;color:#A0A0A0;font-style:italic;font-size:80%'>".htmlEncode ( $replyMail)."</div><div style='background:#F0F0F0;color:#F0F0F0;font-size:1pt;'>###PROJEQTOR###</div>
          <a href='". $ref ."' target='#' style='color:white;display:none;'>". i18n ( get_class ( $this ) ) ." #". htmlEncode ( $this->id ) ."</a></td></tr></table>"
          .$templateToReplace;
  }
  
  return preg_replace_callback('(\$\{[a-zA-Z0-9_\-]+\})',
    function ($matches) {
      global $lastEmailChangeDate;
      $property = trim($matches[0], '${}');
      if (property_exists($this, $property)) {
        if (isset($this, $property) and $this->$property != '') {
          if (substr($property,-8)=='DateTime') {
            return htmlFormatDateTime($this->$property,false, true);
          } else if (substr($property,-4)=='Date') {
            return htmlFormatDate($this->$property,true);
          } else {
            return $this->$property;
          }
        } else {
          return "-";
        }
      } else if (substr($property,0,4)=='name' and $property!='name' and substr($property,4,1)==strtoupper(substr($property,4,1)) ){
        $cls=substr($property,4);
        $fld='id'.$cls;
        if (strpos($property,'__id')>0) {
          $expl=explode('__',$property);
          $cls=substr($expl[1],2);
        }
        if (property_exists($this, $fld)) {
          if ($cls=='User' or $cls=='Resource' or $cls=='Contact') $cls='Affectable';
          return SqlList::getNameFromId($cls, $this->$fld);
        } else {
          return "\$$fld not a property to define $property of " . get_class($this);
        }
      } else if (substr($property,-4)=='Date' and property_exists($this, $property.'Time')) {
        $propertyTime=$property.'Time';
        return htmlFormatDate($this->$propertyTime,true);
      } else if ($property == 'responsible' and property_exists($this, 'idResource')) { 
        return SqlList::getNameFromId('Affectable', $this->idResource);
      } else if ($property == 'dbName') {
        return Parameter::getGlobalParameter('paramDbName');
      } else if ($property == 'goto') {
        $goto = $this->getReferenceUrl ();
        return '<a href="' . $goto . '">'.i18n(get_class($this)) . ' #' . $this->id . '</a>';
      } else if ($property == "project") {
        if (property_exists($this, 'idProject')) {
          return SqlList::getNameFromId('Project', $this->idProject);
        } else {
          return "-";
        }
      }else if($property == 'ASSIGNMENT'){
        return $this->getAssignmentHtmlTab();
      } else if ($property == 'sender') {
        return SqlList::getNameFromId('Affectable', getCurrentUserId());
      } else if ($property == 'url') {
        return $this->getReferenceUrl();
      } else if ($property == 'HISTORY') {
        return $this->getLastChangeTabForObject($this,$lastEmailChangeDate);
      } else if ($property == 'HISTORYFULL') {
      	return $this->getLastChangeTabForObject($this,'full');        
      } else if ($property == 'LINK') {
        return $this->getLinksHtmlTab();
      } else if ($property == 'NOTE') {
        return $this->getNotesHtmlTab();
      }else if($property == 'NOTESTD'){
        //florent
        $rowStart = '<tr>';
        $rowEnd = '</tr>';
        $labelStart = '<td style="background:#DDDDDD;font-weight:bold;text-align: right;width:25%;vertical-align: middle;">&nbsp;&nbsp;';
        $labelEnd = '&nbsp;</td>';
        $fieldStart = '<td style="width:2px;">&nbsp;</td><td style="background:#FFFFFF;text-align: left;">';
        $fieldEnd = '</td>';
        $sectionStart = '<td colspan="3" style="background:#555555;color: #FFFFFF; text-align: center;font-size:10pt;font-weight:normal;">';
        $sectionEnd = '</td>';
        $notes = '<table style="font-size:9pt; width: 95%;font-family: Verdana, Arial, Helvetica, sans-serif;">';
        $notes =$this->getNotesClassicTab($notes, $rowStart, $rowEnd, $sectionStart, $sectionEnd, $labelStart, $labelEnd, $fieldStart, $fieldEnd);
        $notes .='</table>';
        return $notes;
      }else if ($property == 'allAttachments') {
        return;
      } else if ($property == 'lastAttachment') {
      	return;
      }
       else {
        return "\$$property not a property of " . get_class($this);
      }
    },
    $templateToReplace);
}
  
  
  public function getReferenceUrl() {
    $ref = self::getBaseUrl ();
    $ref .= '/view/main.php?directAccess=true&objectClass=' . get_class ( $this ) . '&objectId=' . $this->id;
    return $ref;
  }

  /**
   * =========================================================================
   * Specific function added to setup a workaround for bug #305
   * waiting for Dojo fixing (Dojo V1.6 ?)
   *
   * @todo : deactivate this function if Dojo fixed.
   */
  public function recalculateCheckboxes($force = false) {
    // if no status => nothing to do
    if (! property_exists ( $this, 'idStatus' )) {
      return;
    }
    $status = new Status ( $this->idStatus );
    // if no type => nothing to do
    $class=get_class($this);
    if ($class=='TicketSimple') $class='Ticket';
    $fldType = 'id' . $class . 'Type';
    $typeClass = $class . 'Type';
    if (! property_exists ( $this, $fldType )) {
      return;
    }
    $type = new $typeClass ( $this->$fldType );
    //BEGIN - CHANGE qCazelles #187
    //Old
//     if (((property_exists ( $type, 'lockHandled' ) and $type->lockHandled) or $force) and property_exists ( $this, 'handled' )) {
//       if ($status->setHandledStatus) {
//         $this->handled = 1;
//         if (property_exists ( $this, 'handledDate' ) and ! $this->handledDate)
//           $this->handledDate = date ( "Y-m-d" );
//         if (property_exists ( $this, 'handledDateTime' ) and ! $this->handledDateTime)
//           $this->handledDateTime = date ( "Y-m-d H:i:s" );
//       } else {
//         $this->handled = 0;
//       }
//     }
    //New
    if (((property_exists ( $type, 'lockHandled' ) and $type->lockHandled) or $force)) {
      if (property_exists ( $this, 'handled' )) {
        if ($status->setHandledStatus) {
          $this->handled = 1;
          if (property_exists ( $this, 'handledDate' ) and ! $this->handledDate)
            //florent  4093
            $this->handledDate = date ( "Y-m-d" );
           if (property_exists ( $this, '_Assignment' )){
             foreach ($this->_Assignment as $id=>$ass){
               if(isset($ass->realStartDate)){
                 $this->handledDate=$ass->realStartDate;
               }
             }
           }
            if (property_exists ( $this, 'handledDateTime' ) and ! $this->handledDateTime)
              $this->handledDateTime = date ( "Y-m-d H:i:s" );
        } else {
          $this->handled = 0;
        }
      } else if (property_exists ( $this, 'isStarted' )) {
        if ($status->setHandledStatus) {
          $this->isStarted = 1;
          if (property_exists ( $this, 'realStartDate' ) and ! $this->realStartDate)
            $this->realStartDate = date ( "Y-m-d" );
        }
      }
    }
    //Old
//     if (((property_exists ( $type, 'lockDone' ) and $type->lockDone) or $force) and property_exists ( $this, 'done' )) {
//       if ($status->setDoneStatus) {
//         $this->done = 1;
//         if (property_exists ( $this, 'doneDate' ) and ! $this->doneDate)
//           $this->doneDate = date ( "Y-m-d" );
//         if (property_exists ( $this, 'doneDateTime' ) and ! $this->doneDateTime)
//           $this->doneDateTime = date ( "Y-m-d H:i:s" );
//       } else {
//         $this->done = 0;
//       }
//     }
    //New
    if (((property_exists ( $type, 'lockDone' ) and $type->lockDone) or $force)) {
      if (property_exists ( $this, 'done' )) {
        if ($status->setDoneStatus) {
          $this->done = 1;
          if (property_exists ( $this, 'doneDate' ) and ! $this->doneDate)
            $this->doneDate = date ( "Y-m-d" );
            if (property_exists ( $this, 'doneDateTime' ) and ! $this->doneDateTime)
              $this->doneDateTime = date ( "Y-m-d H:i:s" );
        } else {
          $this->done = 0;
        }
      } else if (property_exists ( $this, 'isDelivered' )) {
        if ($status->setDoneStatus) {
          $this->isDelivered = 1;
          if (property_exists ( $this, 'realDeliveryDate' ))
            $this->realDeliveryDate = date ( "Y-m-d" );
        }
      }
    }
    //END - CHANGE qCazelles #187
    if (((property_exists ( $type, 'lockSolved' ) and $type->lockSolved) or $force) and property_exists ( $this, 'solved' ) and property_exists ( $this, 'idResolution' )) {
      $resolution = new Resolution ( $this->idResolution );
      if ($resolution->solved) {
        $this->solved = 1;
      } else {
        $this->solved = 0;
      }
    }
    if (((property_exists ( $type, 'lockIdle' ) and $type->lockIdle) or $force) and property_exists ( $this, 'idle' )) {
      if (! self::isSaveConfirmed ()) {
        // If save confirmed, must not override idle status that is cascaded
        if ($status->setIdleStatus) {
          $this->idle = 1;
          if (property_exists ( $this, 'idleDate' ) and ! $this->idleDate)
            $this->idleDate = date ( "Y-m-d" );
          if (property_exists ( $this, 'idleDateTime' ) and ! $this->idleDateTime)
            $this->idleDateTime = date ( "Y-m-d H:i:s" );
          //BEGIN - ADD qCazelles #187
          if (property_exists ( $this, 'realEndDate' ) and ! $this->realEndDate)
            $this->realEndDate = date ( "Y-m-d" );
          //END -ADD qCazelles #187
        } else {
          $this->idle = 0;
        }
      }
    }
    if (((property_exists ( $type, 'lockCancelled' ) and $type->lockCancelled) or $force) and property_exists ( $this, 'cancelled' )) {
      $this->cancelled = ($status->setCancelledStatus) ? 1 : 0;
    }
    //BEGIN - ADD qCazelles #187
    if (((property_exists ( $this, 'lockIntoService' ) and $type->lockIntoService) or $force) and property_exists ( $this, 'isEis' )) {
      if ($status->setIntoserviceStatus) {
        $this->isEis = 1;
        if (property_exists ( $this, 'realEisDate' ) and ! $this->realEisDate)
          $this->realEisDate = date ( "Y-m-d" );
      }
    }
    //END -ADD qCazelles #187
  }

  public function getAlertLevel($withIndicator = false) {
    $crit = array('refType' => get_class ( $this ), 'refId' => $this->id);
    $indVal = new IndicatorValue ();
    $lst = $indVal->getSqlElementsFromCriteria ( $crit, false );
    $level = "NONE";
    $desc = '';
    foreach ( $lst as $indVal ) {
      if ($indVal->warningSent and $level != "ALERT") {
        $level = "WARNING"; // Over warning value
      }
      if ($indVal->alertSent) {
        $level = "ALERT"; // Over alert value
      }
      if ($indVal->status == "KO") {
        // $level="OVER"; // Over target value
      }
      if ($withIndicator and ($indVal->warningSent or $indVal->alertSent)) {
        if ($desc == '')
          $desc .= '<div style="font-size:80%;color:#555555;">' . i18n ( 'colIdIndicator' ) . '&nbsp;:</div>';
        $color = ($indVal->alertSent) ? "#FFAAAA" : "#FFDDAA";
        $desc .= '<div style="font-size:80%;background-color:' . $color . ';padding:2px 5px;margin:3px 0px 2px 0px;border:1px solid #aaaaaa">' . $indVal->getShortDescription () . '</div>';
        // $indDesc=$indVal->getShortDescriptionArray();
        // $desc.=$indDesc['indicator'];
        // $desc.=$indDesc['target'];
      }
    }
    return array('level' => $level, 'description' => $desc);
  }

  public function buildSelectClause($included = false, $hidden = array(), $dep = array(), $parent = null) {
    $table = $this->getDatabaseTableName ();
    $select = "";
    $from = "";
    if (self::is_subclass_of ( $this, 'PlanningElement' )) {
      $this->setVisibility ();
    }
    foreach ( $this as $col => $val ) {
      $firstCar = substr ( $col, 0, 1 );
      $threeCars = substr ( $col, 0, 3 );
      if (! isset ( $dep [$col] ) and ! isset ( $dep ['root'] ) and (($included and ($col == 'id' or $threeCars == 'ref' or $threeCars == 'top' or $col == 'idle')) or ($firstCar == '_') or (strpos ( $this->getFieldAttributes ( $col ), 'hidden' ) !== false and strpos ( $this->getFieldAttributes ( $col ), 'forceExport' ) === false) or ($col == 'password') or (isset ( $hidden [$col] )) or (strpos ( $this->getFieldAttributes ( $col ), 'noExport' ) !== false) or (strpos ( $this->getFieldAttributes ( $col ), 'calculated' ) !== false))) 
      // or ($costVisibility!="ALL" and (substr($col, -4,4)=='Cost' or substr($col,-6,6)=='Amount') )
      // or ($workVisibility!="ALL" and (substr($col, -4,4)=='Work') )
      // or calculated field : not to be fetched
      {
        // Here are all cases of not dispalyed fields
      } else if ($included and ((isset ( $dep ['root'] ) and isset ( $hidden [$dep ['root'] . $col] )) or ($parent and ($col == 'refType' or $col == 'refId' or $col == 'id' . $parent)))) {
        //
      } else if ($this->isAttributeSetToField ( $col, 'noExport' ) or $this->isAttributeSetToField ( $col, 'calculated' )) {
        //
      } else if ($firstCar == ucfirst ( $firstCar ) or isset ( $dep [$col] )) {
        $classDep = ltrim ( $col, '_' );
        if (! SqlElement::class_exists ( $classDep ))
          continue;
        $ext = new $classDep ();
        if (isset ( $dep [$col] )) {
          $cpt = 0;
          foreach ( $ext as $tstCol => $tstVal ) {
            if (! isset ( $hidden [$classDep . '_' . $tstCol] ) and substr ( $tstCol, 0, 1 ) != '_' and $tstCol != 'refType' and $tstCol != 'refId' and $tstCol != 'id' . get_class ( $this )) {
              $cpt ++;
            }
          }
          if ($cpt == 0)
            continue;
        }
        if ((property_exists ( $classDep, 'refType' ) and property_exists ( $classDep, 'refId' )) or property_exists ( $classDep, 'id' . get_class ( $this ) )) {
          $from .= ' left join ' . $ext->getDatabaseTableName ();
          if (property_exists ( $classDep, 'refType' ) and property_exists ( $classDep, 'refId' )) {
            $from .= ' on ' . $table . ".id" . ' = ' . $ext->getDatabaseTableName () . '.refId' . ' and ' . $ext->getDatabaseTableName () . ".refType='" . get_class ( $this ) . "'";
          } else if (property_exists ( $classDep, 'id' . get_class ( $this ) )) {
            $from .= " on $table.id=" . $ext->getDatabaseTableName () . ".id" . get_class ( $this );
            if ($classDep == 'DocumentVersion' and isset ( $hidden ['documentVersionAll'] )) {
              $from .= " and $table.idDocumentVersion=" . $ext->getDatabaseTableName () . ".id";
            }
          }
          if (isset ( $dep [$col] )) {
            $extClause = $ext->buildSelectClause ( true, $hidden, array('root' => $classDep . '_'), get_class ( $this ) );
          } else {
            $extClause = $ext->buildSelectClause ( true, $hidden );
          }
          if (trim ( $extClause ['select'] )) {
            $select .= ', ' . $extClause ['select'];
          }
        }
      } else {
        $select .= ($select == '') ? '' : ', ';
        $root = ($included and isset ( $dep ['root'] )) ? strtolower ( $dep ['root'] ) : '';
        $select .= $table . '.' . $this->getDatabaseColumnName ( $col ) . ' as ' . $root . $col;
      }
    }
    return array('select' => $select, 'from' => $from);
  }

  public function setReference($force = false, $old = null, $fmtPrefix=null, $fmtSuffix=null, $fmtNumber=null) {
    scriptLog ( 'SqlElement::setReference' );
    $objectsWithFixedReference = array('Bill');
    if (! property_exists ( $this, 'reference' )) {
      return;
    }
    $class = get_class ( $this );
    if ($class == 'TicketSimple')
      $class = 'Ticket';
    if ($class == 'Bill' and ! $this->billId)
      return; // Do not set Reference until billId is set
    
    if (!$fmtPrefix) $fmtPrefix = Parameter::getGlobalParameter ( 'referenceFormatPrefix' );
    if (!$fmtSuffix) $fmtSuffix = '';
    if (!$fmtNumber) $fmtNumber = Parameter::getGlobalParameter ( 'referenceFormatNumber' );
    if ($class == 'Bill') {
      $fmtPrefixBill = Parameter::getGlobalParameter ( 'billReferenceFormat' );
      $fmtNumberBill = Parameter::getGlobalParameter ( 'billNumSize' );
      if ($fmtPrefixBill)
        $fmtPrefix = $fmtPrefixBill;
      if ($fmtNumberBill)
        $fmtNumber = $fmtNumberBill;
    }
    $posNume = strpos ( $fmtPrefix, '{NUME}' );
    if ($posNume !== false) {
      $fmtSuffix = substr ( $fmtPrefix, $posNume + 6 );
      $fmtPrefix = substr ( $fmtPrefix, 0, $posNume );
    }
    $change = Parameter::getGlobalParameter ( 'changeReferenceOnTypeChange' );
    $type = 'id' . $class . 'Type';
    if ($this->reference and ! $force) {
      if ($change != 'YES') {
        return;
      }
      if (! property_exists ( $this, $type )) {
        return;
      }
      if (! property_exists ( $this, 'idProject' )) {
        return;
      }
      if (! $old) {
        $old = new $class ( $this->id );
      }
      if (!property_exists($this, 'idProduct')) {
        if ($this->$type == $old->$type and $this->idProject == $old->idProject) {
          return;
        }
      } else {
        if ($this->$type == $old->$type and $this->idProject == $old->idProject and $this->idProduct == $old->idProduct) {
          return;
        }
      }
      if (in_array ( get_class ( $this ), $objectsWithFixedReference )) {
        return;
      }
    }
    if (isset ( $this->idProject )) {
      $projObj = new Project ( $this->idProject );
    } else {
      $projObj = new Project ();
    }
    if (property_exists($this, 'idProduct') and isset($this->idProduct)) {
    	$prodObj = new Product ( $this->idProduct );
    } else {
    	$prodObj = new Product ();
    }
    if (isset ( $this->$type )) {
      $typeObj = new Type ( $this->$type );
    } else {
      $typeObj = new Type ();
    }
    $year = date ( 'Y' );
    $month = date ( 'm' );
    if (get_class ( $this ) == 'Bill') {
      $year = substr ( $this->date, 0, 4 );
      $month = substr ( $this->date, 5, 2 );
    } else if (property_exists ( $this, 'sendDate' ) and $this->sendDate) {
      $year = substr ( $this->sendDate, 0, 4 );
      $month = substr ( $this->sendDate, 5, 2 );
    } else if (property_exists ( $this, 'creationDate' )) {
      $year = substr ( $this->creationDate, 0, 4 );
      $month = substr ( $this->creationDate, 5, 2 );
    } else if (property_exists ( $this, 'creationDateTime' )) {
      $year = substr ( $this->creationDateTime, 0, 4 );
      $month = substr ( $this->creationDateTime, 5, 2 );
    }
    $arrayFrom = array('{PROD}', '{PROJ/PROD}','{PROD/PROJ}','{PROJ}', '{TYPE}', '{YEAR}', '{MONTH}');
    $arrayTo = array(
    		$prodObj->designation,
    		($projObj->projectCode)?$projObj->projectCode:$prodObj->designation,
    		($prodObj->designation)?$prodObj->designation:$projObj->projectCode,
        $projObj->projectCode, 
        $typeObj->code, 
        $year, 
        $month);
    $prefix = str_replace ( $arrayFrom, $arrayTo, $fmtPrefix );
    $suffix = str_replace ( $arrayFrom, $arrayTo, $fmtSuffix );
    $query = "select max(reference) as ref from " . $this->getDatabaseTableName ();
    $query .= " where reference like '" . $prefix . "%'";
    $query .= " and length(reference)=( select max(length(reference)) from " . $this->getDatabaseTableName ();
    $query .= " where reference like '" . $prefix . "%')";
    $ref = $prefix;
    $mutex = new Mutex ( $prefix );
    $mutex->reserve ();
    $result = Sql::query ( $query );
    $numMax = '0';
    if ($result) {
      $line = Sql::fetchLine ( $result );
      $refMax = $line ['ref'];
      $numMax = intval(substr ( $refMax, strlen ( $prefix ) ));
    }
    $numMax += 1;
    if ($fmtNumber and $fmtNumber - strlen ( $numMax ) > 0) {
      $num = substr ( '0000000000', 0, $fmtNumber - strlen ( $numMax ) ) . $numMax;
    } else {
      $num = $numMax;
    }
    $this->reference = $prefix . $num . $suffix;
    if (get_class ( $this ) == 'Document' and property_exists ( $this, 'documentReference' )) {
      $fmtDocument = Parameter::getGlobalParameter ( 'documentReferenceFormat' );
      $docRef = str_replace ( array('{PROD}', '{PROJ/PROD}','{PROD/PROJ}','{PROJ}', '{TYPE}', '{NUM}', '{NAME}'), array(
          $prodObj->designation,
          ($projObj->projectCode)?$projObj->projectCode:$prodObj->designation,
      		($prodObj->designation)?$prodObj->designation:$projObj->projectCode,
          $projObj->projectCode, 
          $typeObj->code, 
          $num, 
          $this->name), $fmtDocument );
      $this->documentReference = $docRef;
    }
    if ($force) {
      $this->updateSqlElement ();
    }
    $mutex->release ();
  }

  public function setDefaultResponsible() {
    if (get_class ( $this ) != 'Project' and property_exists ( $this, 'idResource' ) and property_exists ( $this, 'idProject' ) and ! trim ( $this->idResource ) and trim ( $this->idProject )) {
      if (Parameter::getGlobalParameter ( 'setResponsibleIfSingle' ) == "YES") {
        $aff = new Affectation ();
        $crit = array('idProject' => $this->idProject);
        $cpt = $aff->countSqlElementsFromCriteria ( $crit );
        if ($cpt == 1) {
          $aff = SqlElement::getSingleSqlElementFromCriteria ( 'Affectation', $crit );
          $res = new Resource ( $aff->idResource );
          if ($res and $res->id) {
            $this->idResource = $res->id;
          }
        }
      }
    }
  }

  public function getTitle($col) {
    if ($this->getFieldTooltip($col)) return ''; // If field has hint, don't show title
    
    $arrayTest = array('title', 'col', '');
    foreach ( $arrayTest as $testPrefix ) {
      $test = $testPrefix . ucfirst ( $col );
      $testTranslation = i18n ( $test );
      if ($testTranslation != "[$test]")
        return $testTranslation;
    }
    return i18n ( $col );
  }

  public static function unsetRelationShip($rel1, $rel2) {
    unset ( self::$_relationShip [$rel1] [$rel2] );
  }

  public function getOld($withoutDependingElemnts=true) {
    $class = get_class ( $this );
    return new $class ( $this->id, $withoutDependingElemnts);
  }

  public function splitLongFields() {
    $maxLenth = 500;
    foreach ( $this as $fld => $val ) {
      if ($this->getDataLength ( $fld ) > 100 and strlen ( $val ) > $maxLenth) {
        // $secFull="_sec_".$fld;
        // $this->$secFull=$val;
        $fldFull = "_" . $fld . "_full";
        $this->$fldFull = $val;
        $this->$fld = substr ( $val, 0, $maxLenth ) . ' (...)';
      }
    }
  }

  public static function isVisibleField($col) {
    // Check if cost and work field is visible for profile
    $cost = (substr ( $col, - 4 ) == 'Cost' or substr ( $col, - 6 ) == "Amount") ? true : false;
    $work = (substr ( $col, - 4 ) == 'Work') ? true : false;
    if (! $cost and ! $work) {
      return true;
    }
    if (! self::$staticCostVisibility or ! self::$staticWorkVisibility) {
      $pe = new PlanningElement ();
      $pe->setVisibility ();
      self::$staticCostVisibility = $pe->_costVisibility;
      self::$staticWorkVisibility = $pe->_workVisibility;
    }
    $costVisibility = self::$staticCostVisibility;
    $workVisibility = self::$staticWorkVisibility;
    $validated = (substr ( $col, 0, 9 ) == 'validated') ? true : false;
    if ($cost) {
      if ($costVisibility == 'ALL') {
        return true;
      } else if ($costVisibility == 'NO') {
        return false;
      } else if ($costVisibility == 'VAL') {
        if ($validated) {
          return true;
        } else {
          return false;
        }
      } else {
        errorLog ( "ERROR : costVisibility='$costVisibility' is not 'ALL', 'NO' or 'VAL'" );
      }
    } else if ($work) {
      if ($workVisibility == 'ALL') {
        return true;
      } else if ($workVisibility == 'NO') {
        return false;
      } else if ($workVisibility == 'VAL') {
        if ($validated) {
          return true;
        } else {
          return false;
        }
      } else {
        errorLog ( "ERROR : workVisibility='$workVisibility' is not 'ALL', 'NO' or 'VAL'" );
      }
    }
    return true;
  }

  public static function setDeleteConfirmed() {
    self::$staticDeleteConfirmed = true;
  }

  public static function isDeleteConfirmed() {
    return self::$staticDeleteConfirmed;
  }

  public static function setSaveConfirmed() {
    self::$staticSaveConfirmed = true;
  }

  public static function isSaveConfirmed() {
    return self::$staticSaveConfirmed;
  }

  public static function isThumbableField($col) {
// MTY - LEAVE SYSTEM      
//    return ($col == 'idResource' or $col == 'idUser' or $col == 'idContact'  or $col=='idAccountable' or $col=='idResponsible') ? true : false;
    return ($col == 'idEmployee' or $col == 'idResource' or $col == 'idUser' or $col == 'idContact'  or $col=='idAccountable' or $col=='idResponsible') ? true : false;
// MTY - LEAVE SYSTEM      
  }

  public static function isColorableField($col) {
// BEGIN - CHANGE BY TABARY - NOTIFICATION SYSTEM      
//    return ($col == 'idProject' or $col == 'idStatus' or $col == 'idQuality' or $col == 'idHealth' or $col == 'idTrend' or $col == 'idLikelihood' or $col == 'idCriticality' or $col == 'idSeverity' or $col == 'idUrgency' or $col == 'idPriority' or $col == 'idRiskLevel' or $col == 'idFeasibility' or $col == 'idEfficiency' or $col == 'idResolution' or $col == 'idTenderStatus' or $col == 'idDeliverableWeight' or $col == 'idDeliverableStatus' or $col == 'idIncomingWeight' or $col == 'idIncomingStatus') ? true : false;
    return ($col == 'idStatusNotification' or $col == 'idProject' or $col == 'idStatus' or $col == 'idQuality' or $col == 'idHealth' or $col == 'idTrend' or $col == 'idLikelihood' or $col == 'idCriticality' or $col == 'idSeverity' or $col == 'idUrgency' or $col == 'idPriority' or $col == 'idRiskLevel' or $col == 'idFeasibility' or $col == 'idEfficiency' or $col == 'idResolution' or $col == 'idTenderStatus' or $col == 'idDeliverableWeight' or $col == 'idDeliverableStatus' or $col == 'idIncomingWeight' or $col == 'idIncomingStatus') ? true : false;
// END - CHANGE BY TABARY - NOTIFICATION SYSTEM      
  }

  public static function isIconableField($col) {
    return ($col == 'idQuality' or $col == 'idHealth' or $col == 'idTrend' or $col=='idAssetType') ? true : false;
  }

  public function getExtraRequiredFields($newType = "", $newStatus = "", $newPlanningMode = "", $profile = null) {
    $result = array();
    $type = $newType;
    $status = $newStatus;
    $planningMode = $newPlanningMode;
    $user = getSessionUser ();
    $class = get_class ( $this );
// MTY - LEAVE SYSTEM    
    if ($class=="Leave") { return $result;}
// MTY - LEAVE SYSTEM    
    $testObj = $this;
    $testClass = $class;
    $typeFld = 'id' . $class . "Type";
    if ($class=='PeriodicMeeting') $typeFld='idMeetingType';
    if (SqlElement::is_a ( $this, 'PlanningElement' )) {
      if ($this->refType) {
        $testClass = $this->refType;
      } else {
        $testClass = str_replace ( 'PlanningElement', '', $class );
      }
      if ($testClass and SqlElement::class_exists ( $testClass ))
        $testObj = new $testClass ( $this->refId, true );
      $planningModeName = 'id' . str_replace ( 'PlanningElement', '', get_class ( $this ) ) . 'PlanningMode';
      if (!$planningMode and property_exists($this, $planningModeName)) {
        $planningMode=$this->$planningModeName;
      }
    } else if ($class == 'WorkElement') {
      if ($this->refType) {
        $testClass = $this->refType;
      } else {
        $testClass = 'Ticket';
      }
      if ($testClass and SqlElement::class_exists ( $testClass ))
        $testObj = new $testClass ( $this->refId, true );
    }
    if (! $profile) $profile = $user->getProfile ( $this );
    if ($this->id) {
      $typeName = 'id' . str_replace ( 'PlanningElement', '', get_class ( $this ) ) . 'Type';
      $planningModeName = 'id' . str_replace ( 'PlanningElement', '', get_class ( $this ) ) . 'PlanningMode';
      if (! $type and property_exists ( $testObj, $typeName )) {
        $type = $testObj->$typeName;
      }
      if (! $status and property_exists ( $testObj, 'idStatus' )) {
        $status = $testObj->idStatus;
      }
      if (! $planningMode and property_exists ( $this, $planningModeName )) {
        $planningMode = $this->$planningModeName;
      }
    } else {
      $typeName = 'id' . str_replace ( 'PlanningElement', '', get_class ( $this ) ) . 'Type';
      $typeClassName = str_replace ( 'PlanningElement', '', get_class ( $this ) ) . 'Type';
      if (! $status) {
        $status=$this->getFirstStatus($type);
      }
      $planningModeName = 'id' . str_replace ( 'PlanningElement', '', get_class ( $this ) ) . 'PlanningMode';
      $typeElt = null;
      if (! $type and SqlElement::class_exists ($typeClassName) and $typeClassName!="ExpenseDetailType") {
        $typeList = SqlList::getList ( $typeClassName );
        $typeElt = reset ( $typeList );
        $type = ($typeElt) ? key ( $typeList ) : null;
      }
      if (! $planningMode and $type and SqlElement::class_exists($typeClassName) and property_exists ( $typeClassName, $planningModeName )) {
        $typeObj = new $typeClassName ( $type );
        $planningMode = $typeObj->$planningModeName;
      }
    }
    if ($planningMode and $planningMode != '*') {
      $planningModeObj = new PlanningMode ( $planningMode );
      if ($planningModeObj->mandatoryStartDate and property_exists ( $this, 'validatedStartDate' )) {
        $result ['validatedStartDate'] = 'required';
      }
      if ($planningModeObj->mandatoryEndDate and property_exists ( $this, 'validatedEndDate' )) {
        $result ['validatedEndDate'] = 'required';
      }
      if ($planningModeObj->mandatoryDuration and property_exists ( $this, 'validatedDuration' )) {
        $result ['validatedDuration'] = 'required';
      }
    }
    if ($type and $type != '*') {
      $typeObj = new Type ( $type );
      if ($typeObj->mandatoryResourceOnHandled) {
        if ($newStatus and $newStatus != '*') {
          $statusObj = new Status ( $newStatus );
          if ($statusObj->setHandledStatus) {
            $result ['idResource'] = 'required';
          }
        } else {
          if (property_exists ( $this, 'handled' ) and $this->handled) {
            $result ['idResource'] = 'required';
          }
        }
      }
      if ($typeObj->mandatoryDescription) {
        $result ['description'] = 'required';
      }
      if ($typeObj->mandatoryResultOnDone) {
        if ($newStatus and $newStatus != '*') {
          $statusObj = new Status ( $newStatus );
          if ($statusObj->setDoneStatus) {
            $result ['result'] = 'required';
          }
        } else {
          if (property_exists ( $this, 'done' ) and $this->done) {
            $result ['result'] = 'required';
          }
        }
      }
      if (property_exists ( $typeObj, 'mandatoryResolutionOnDone' ) and $typeObj->mandatoryResolutionOnDone) {
        if ($newStatus and $newStatus != '*') {
          $statusObj = new Status ( $newStatus );
          if ($statusObj->setDoneStatus) {
            $result ['idResolution'] = 'required';
          }
        } else {
          if (property_exists ( $this, 'done' ) and $this->done) {
            $result ['idResolution'] = 'required';
          }
        }
      }
    }
    // Add extra result from plugin
    $extraResult = self::getExtraRequiredFieldsFullList ();
    $scopeArray = array('Type', 'Status', 'Profile');
    foreach ( $scopeArray as $scope ) {
      $fld = strtolower ( $scope );
      if ($$fld and $$fld != '*' and isset ( $extraResult [$scope] [get_class ( $this )] [$$fld] )) {
        foreach ( $extraResult [$scope] [get_class ( $this )] [$$fld] as $field ) {
          $result [$field] = 'required';
        }
      }
    }
    /*
     * // TEST
     * $list=self::getExtraHiddenFieldsFullList();
     * $listType=array();
     * $listStatus=array();
     * $listProfile=array();
     * if ($type and $type!='*') {
     * $type=($newType)?$newType:$testObj->$typeFld;
     * if (isset($list['Type']) and isset($list['Type'][$class]) and isset($list['Type'][$class][$type]) ) {
     * $listType=$list['Type'][$class][$type];
     * }
     * }
     * if (property_exists($testObj,'idStatus') and $newStatus!='*') {
     * $status=($newStatus)?$newStatus:$testObj->idStatus;
     * if (isset($list['Status']) and isset($list['Status'][$class]) and isset($list['Status'][$class][$status]) ) {
     * $listStatus=$list['Status'][$class][$status];
     * }
     * }
     * if ($newProfile!='*') {
     * if ($newProfile) {
     * $profile=$newProfile;
     * } else {
     * $profile=getSessionUser()->getProfile($this);
     * }
     * if (isset($list['Profile']) and isset($list['Profile'][$class]) and isset($list['Profile'][$class][$profile]) ) {
     * $listProfile=$list['Profile'][$class][$profile];
     * }
     * }
     * // TEST
     */
    
    return $result;
  }

  public function getExtraHiddenFields($newType = "", $newStatus = "", $newProfile = "", $forExport=false) {
    $class = get_class ( $this );
// MTY - LEAVE SYSTEM    
    if ($class=="Leave") { return array();}
// MTY - LEAVE SYSTEM    
    $testObj = $this;
    $testClass = $class;
    $typeFld = 'id' . $class . "Type";
    if (SqlElement::is_a ( $this, 'PlanningElement' )) {
      if ($this->refType) {
        $testClass = $this->refType;
      } else {
        $testClass = str_replace ( 'PlanningElement', '', $class );
      }
      $testObj = new $testClass ( $this->refId, true );
    } else if ($class == 'WorkElement') {
      if ($this->refType) {
        $testClass = $this->refType;
      } else {
        $testClass = 'Ticket';
      }
      $testObj = new $testClass ( $this->refId, true );
    }
    $typeClass = $testClass . 'Type';
    $typeFld = 'id' . $typeClass;
    if ($class == 'TicketSimple')
      $typeFld = 'idTicketType';
    $list = self::getExtraHiddenFieldsFullList ();
    $listType = array();
    $listStatus = array();
    $listProfile = array();
    $listModule = array();
    if (property_exists ( $testObj, $typeFld ) and $newType != '*') {
      $type = ($newType) ? $newType : $testObj->$typeFld;
      if (isset ( $list ['Type'] ) and isset ( $list ['Type'] [$class] ) and isset ( $list ['Type'] [$class] [$type] )) {
        $listType = $list ['Type'] [$class] [$type];
      }
    }
    if (property_exists ( $testObj, 'idStatus' ) and $newStatus != '*') {
      $status = ($newStatus) ? $newStatus : $testObj->idStatus;
      if (! $status) {
        $status=$this->getFirstStatus($newType);
      }
      if (isset ( $list ['Status'] ) and isset ( $list ['Status'] [$class] ) and isset ( $list ['Status'] [$class] [$status] )) {
        $listStatus = $list ['Status'] [$class] [$status];
      }
    }
    if ($newProfile != '*') {
      if ($newProfile) {
        $profile = $newProfile;
      } else {
        $profile = getSessionUser ()->getProfile ( $this );
      }
      if (isset ( $list ['Profile'] ) and isset ( $list ['Profile'] [$class] ) and isset ( $list ['Profile'] [$class] [$profile] )) {
        $listProfile = $list ['Profile'] [$class] [$profile];
      }
    }
    // if ($newStatus=='*' and $newProfile=='*') return $listType;
    // if ($newType=='*' and $newProfile=='*') return $listStatus;
    // if ($newType=='*' and $newStatus=='*') return $listProfile;
    $currentScript=basename($_SERVER['PHP_SELF'], '.php'); 
    if (substr($currentScript,0,19)=="screenCustomization") {
      $listModule=array();
    } else {
      $listModule=Module::getListOfFieldsToHide($class);
    }
    
    //if ($forExport) return array_unique ( $listProfile);
    if ($forExport) return array_merge ( $listProfile, $listModule );
    return array_unique ( array_merge ( $listType, $listStatus, $listProfile, $listModule ) );
  }

  private static function getExtraRequiredFieldsFullList() {
    if (self::$_extraRequiredFields != null) {
      return self::$_extraRequiredFields;
    }
    $sessionList = getSessionValue ( 'extraRequiredFieldsArray' );
    if ($sessionList) {
      self::$_extraRequiredFields = $sessionList;
      return self::$_extraRequiredFields;
    }
    $extra = new ExtraRequiredField ();
    $extraList = $extra->getSqlElementsFromCriteria ( null ); // Get all fields
    $result = array('Type' => array(), 'Status' => array(), 'Profile' => array()); // Only scope for Type, Status, Profile
    foreach ( $extraList as $extra ) {
      $sp = explode ( '#', $extra->scope );
      if (count ( $sp ) != 2)
        return array();
      $scope = $sp [0];
      $class = $sp [1];
      if (! isset ( $result [$scope] )) {
        errorLog ( "getExtraRequiredFieldsFullList() : some data has scope '$scope' different from Type, Status, Profile" );
        return array();
      }
      if (! isset ( $result [$scope] [$class] ))
        $result [$scope] [$class] = array();
      if (! isset ( $result [$scope] [$class] [$extra->idType] ))
        $result [$scope] [$class] [$extra->idType] = array();
      $result [$scope] [$class] [$extra->idType] [] = $extra->field;
    }
    self::$_extraRequiredFields = $result;
    setSessionValue ( 'extraRequiredFieldsArray', $result );
    return $result;
  }

  private static function getExtraHiddenFieldsFullList() {
    if (self::$_extraHiddenFields != null) {
      return self::$_extraHiddenFields;
    }
    $sessionList = getSessionValue ( 'extraHiddenFieldsArray' );
    if ($sessionList) {
      self::$_extraHiddenFields = $sessionList;
      return self::$_extraHiddenFields;
    }
    $extra = new ExtraHiddenField ();
    $extraList = $extra->getSqlElementsFromCriteria ( null ); // Get all fields
    $result = array('Type' => array(), 'Status' => array(), 'Profile' => array()); // Only scope for Type, Status, Profile
    foreach ( $extraList as $extra ) {
      $sp = explode ( '#', $extra->scope );
      if (count ( $sp ) != 2)
        return array();
      $scope = $sp [0];
      $class = $sp [1];
      if (! isset ( $result [$scope] )) {
        errorLog ( "getExtraHiddenFieldsFullList() : some data has scope '$scope' different from Type, Status, Profile" );
        return array();
      }
      if (! isset ( $result [$scope] [$class] ))
        $result [$scope] [$class] = array();
      if (! isset ( $result [$scope] [$class] [$extra->idType] ))
        $result [$scope] [$class] [$extra->idType] = array();
      $result [$scope] [$class] [$extra->idType] [] = $extra->field;
    }
    self::$_extraHiddenFields = $result;
    setSessionValue ( 'extraHiddenFieldsArray', $result );
    return $result;
  }

  public function getExtraReadonlyFields($newType = "", $newStatus = "", $newProfile = "") {
    $class = get_class ( $this );
// MTY - LEAVE SYSTEM    
    if ($class=="Leave") { return array();}
// MTY - LEAVE SYSTEM    
    $testObj = $this;
    $testClass = $class;
    $typeFld = 'id' . $class . "Type";
    if (SqlElement::is_a ( $this, 'PlanningElement' )) {
      if ($this->refType) {
        $testClass = $this->refType;
      } else {
        $testClass = str_replace ( 'PlanningElement', '', $class );
      }
      $testObj = new $testClass ( $this->refId, true );
    } else if ($class == 'WorkElement') {
      if ($this->refType) {
        $testClass = $this->refType;
      } else {
        $testClass = 'Ticket';
      }
      $testObj = new $testClass ( $this->refId, true );
    }
    $typeClass = $testClass . 'Type';
    $typeFld = 'id' . $typeClass;
    if ($class == 'TicketSimple')
      $typeFld = 'idTicketType';
    $list = self::getExtraReadonlyFieldsFullList ();
    $listType = array();
    $listStatus = array();
    $listProfile = array();
    if (property_exists ( $testObj, $typeFld ) and $newType != '*') {
      $type = ($newType) ? $newType : $testObj->$typeFld;
      if (isset ( $list ['Type'] ) and isset ( $list ['Type'] [$class] ) and isset ( $list ['Type'] [$class] [$type] )) {
        $listType = $list ['Type'] [$class] [$type];
      }
    }
    if (property_exists ( $testObj, 'idStatus' ) and $newStatus != '*') {
      $status = ($newStatus) ? $newStatus : $testObj->idStatus;
      if (! $status) {
        $status=$this->getFirstStatus($newType);
      }
      if (isset ( $list ['Status'] ) and isset ( $list ['Status'] [$class] ) and isset ( $list ['Status'] [$class] [$status] )) {
        $listStatus = $list ['Status'] [$class] [$status];
      }
    }
    if ($newProfile != '*') {
      if ($newProfile) {
        $profile = $newProfile;
      } else {
        $profile = getSessionUser ()->getProfile ( $this );
      }
      if (isset ( $list ['Profile'] ) and isset ( $list ['Profile'] [$class] ) and isset ( $list ['Profile'] [$class] [$profile] )) {
        $listProfile = $list ['Profile'] [$class] [$profile];
      }
    }
    $listOther=array();
    if (property_exists($class, 'idMilestone') and Parameter::getGlobalParameter('milestoneFromVersion')
    and (   ( property_exists($class, 'idTargetProductVersion') and $this->idTargetProductVersion)
         or ( property_exists($class, 'idProductVersion') and $this->idProductVersion)
        ) ) {
      $pv=new ProductVersion((property_exists($class, 'idTargetProductVersion'))?$this->idTargetProductVersion:$this->idProductVersion);
      if ($pv->idMilestone) {
        $listOther[]='idMilestone';
      }
    }
    return array_unique ( array_merge ( $listType, $listStatus, $listProfile, $listOther) );
  }

  private static function getExtraReadonlyFieldsFullList() {
    if (self::$_extraReadonlyFields != null) {
      return self::$_extraReadonlyFields;
    }
    $sessionList = getSessionValue ( 'extraReadonlyFieldsArray' );
    if ($sessionList) {
      self::$_extraReadonlyFields = $sessionList;
      return self::$_extraReadonlyFields;
    }
    $extra = new ExtraReadonlyField ();
    $extraList = $extra->getSqlElementsFromCriteria ( null ); // Get all fields
    $result = array('Type' => array(), 'Status' => array(), 'Profile' => array()); // Only scope for Type, Status, Profile
    foreach ( $extraList as $extra ) {
      $sp = explode ( '#', $extra->scope );
      if (count ( $sp ) != 2)
        return array();
      $scope = $sp [0];
      $class = $sp [1];
      if (! isset ( $result [$scope] )) {
        errorLog ( "getExtraReadonlyFieldsFullList() : some data has scope '$scope' different from Type, Status, Profile" );
        return array();
      }
      if (! isset ( $result [$scope] [$class] ))
        $result [$scope] [$class] = array();
      if (! isset ( $result [$scope] [$class] [$extra->idType] ))
        $result [$scope] [$class] [$extra->idType] = array();
      $result [$scope] [$class] [$extra->idType] [] = $extra->field;
    }
    self::$_extraReadonlyFields = $result;
    setSessionValue ( 'extraReadonlyFieldsArray', $result );
    return $result;
  }
  
  // ============================================================
  // Redefines standard class test function
  // to avoid error logging when not necessary
  // ============================================================
  public static function is_a($object, $class) {
    global $hideAutoloadError;
    $hideAutoloadError = true; // Avoid error message in autoload
    if (is_object ( $object )) {
      $result = ($object instanceof $class);
    } else if (version_compare ( PHP_VERSION, '5.3.9' ) >= 0) {
      $result = @is_a ( $object, $class, true ); // 3rd parameter "allow_string" is compatible only since V5.3.9
    } else {
      if (self::class_exists ( $object )) {
        $obj = new $object ();
        $result = ($obj instanceof $class);
      } else {
        $result = false;
      }
    }
    $hideAutoloadError = true;
    return $result;
  }

  public static function class_exists($item) {
    global $hideAutoloadError;
    $hideAutoloadError = true; // Avoid error message in autoload
    $result = class_exists ( $item, true );
    $hideAutoloadError = false;
    return $result;
  }

  public static function is_subclass_of($className, $parentClass) {
    global $hideAutoloadError;
    $hideAutoloadError = true; // Avoid error message in autoload
    $result = is_subclass_of ( $className, $parentClass );
    $hideAutoloadError = false;
    return $result;
  }

  public static function getPrivacyClause($obj = null) {
    $isPrivate = 'isPrivate';
    $idUser = 'idUser';
    if ($obj) {
      if (! is_object ( $obj )) {
        $obj = new $obj ();
      }
      $isPrivate = $obj->getDatabaseTableName () . '.' . $obj->getDatabaseColumnName ( 'isPrivate' );
      $idUser = $obj->getDatabaseTableName () . '.' . $obj->getDatabaseColumnName ( 'idUser' );
    }
    return "($isPrivate=0 or $idUser=" . Sql::fmtId ( getSessionUser ()->id ) . ")";
  }
  
  // ADD BY Marc TABARY - 2017-02-24 - TRANSFORM OBJECT SQLELMENT LIST IN ARRAY KEY-NAME
  /**
   * =====================================================================================
   * Transform a collection of objects with class SqlElement to array type key - name
   * --------------------------------------------------------------------------------------
   *
   * @param
   *          sqlElement Objects - $objSqlElt : The objects of class SqlElement to transform
   * @return array key-name
   */
  public static function transformObjSqlElementInArrayKeyName($objSqlElt) {
    if ($objSqlElt == null) {
      return array();
    }
    $theobjSqlElt = $objSqlElt [0];
    $ancestor_class = get_parent_class ( $theobjSqlElt );
    while ( $ancestor_class != false and $ancestor_class != 'SqlElement' ) {
      $ancestor_class = get_parent_class ( $ancestor_class );
    }
    if ($ancestor_class != 'SqlElement') {
      return array();
    }
    
    foreach ( $objSqlElt as $theObj ) {
      $array [$theObj->id] = $theObj->name;
    }
    return $array;
  }
  // END ADD BY Marc TABARY - 2017-02-24 - TRANSFORM OBJECT SQLELMENT LIST IN ARRAY KEY-NAME
  protected function updateMessage($message, $nodataMsg, $status = 'INVALID') {
    $result = new ResultHdl ( ResultHdl::TYPE_UPDATE, $status, $message, null, $nodataMsg = null, $this->id );
    return $result;
  }

  protected function insertMessage($message, $status = 'OK') {
    $result = new ResultHdl ( ResultHdl::TYPE_INSERT, $status, $message, null, null, $this->id );
    return $result;
  }

  protected function copyMessage($message, $newId, $status = 'OK') {
    $result = new ResultHdl ( ResultHdl::TYPE_COPY, $status, $message, null, null, $newId );
    return $result;
  }

  protected function deleteMessage($message, $nodataMsg, $status = 'INVALID') {
    $result = new ResultHdl ( ResultHdl::TYPE_DELETE, $status, $message, null, $nodataMsg, $this->id );
    return $result;
  }

  protected function controlMessage($control, $status = 'INVALID') {
    $result = new ResultHdl ( ResultHdl::TYPE_CONTROL, $status, null, $control, null, $this->id );
    return $result;
  }
  //ADD qCazelles - Filter by status
  public function getExistingStatus() {
  	if (!property_exists($this, 'idStatus')) return array();
    $clsName=get_class($this);
    $arraySessionStatus=array();
    foreach (getAllSessionValues() as $codeSess=>$valSess) {
      if ($valSess=='true' and substr($codeSess,0,10)=='showStatus' and substr($codeSess,strlen($clsName)*(-1))==$clsName) {
        $idx=str_replace(array('showStatus',$clsName),array('',''),$codeSess);
        $arraySessionStatus[$idx]=$idx;
      }
    }
  	$where = 'id in (select distinct idStatus from '.$this->getDatabaseTableName().')';
  	if (count($arraySessionStatus)>0) {
  	  $where.=' or id in '.transformListIntoInClause($arraySessionStatus);
  	}
  	$status=new Status();
  	$list=$status->getSqlElementsFromCriteria(null,null,$where,'sortOrder asc');
  	return $list;
  }
  //END ADD qCazelles - Filter by status

  public static function toArrayList($list,$parent=null,$outputHtml=false) {
    $result=array();
    foreach ($list as $obj) {
      $result[]=$obj->toArrayFields($parent,$outputHtml);
    }
    return $result;
  }
  public function toArrayFields($parent=null,$outputHtml=false) {
    $result=array();
    foreach ($this as $fld=>$value) {
      if (is_object($value)) {
        if (SqlElement::is_a($value,'SqlElement')) {
          $sub=$value->toArrayFields($this,$outputHtml);
          $result[strtolower($fld)]=array($sub);
        }
        continue;
      }
      if (is_array($value)) {
        $pos=strrpos($fld,'_');
        $subClass=substr($fld,1);
        $pos=strpos($subClass,'_');
        if ($pos) {
          $subClass=substr($fld,0,$pos);
        }
        if (SqlElement::class_exists($subClass)) {
          if (strtolower($subClass)=="billline") $value=array_reverse($value);
          $sub=SqlElement::toArrayList($value,$this,$outputHtml);
          $result[strtolower($subClass)]=$sub;
          if ($subClass=='Link') {
            foreach (SqlList::getListNotTranslated('Linkable') as $linkable) {
              $result["link_$linkable"]=array();
            }
            foreach ($sub as $link) {
              $lnkClass=$link['refType'];
              if (!isset($result["link_$lnkClass"])) $result["link_$lnkClass"]=array();
              $result["link_$lnkClass"][]=$link;
            }
          }
        }
        continue;
      }
      if (substr($fld,0,1)=='_') continue;
      $result[$fld]=$value;
      $dataType=$this->getDataType($fld);
      $dataLength=$this->getDataLength($fld);
      if ($dataLength>4000) { // Big text html formatted : must be transformed into plain text
        $text=new Html2Text($value);
        $result[$fld.'Text']=$text->getText();
        $result[$fld]=$value;
      } else if (isForeignKey ($fld, $this)) { // idXxx : also add nameXxx
        $class = substr(foreignKeyWithoutAlias($fld),2);
        $fldName='name'.substr($fld,2);
        if ($class=='Resource' or $class=='User' or $class=='Contact') {
          $result[$fldName]=SqlList::getNameFromId('Affectable', $value);
        } else if (SqlElement::class_exists($class) and property_exists($class,'name')) {
          $result[$fldName]=SqlList::getNameFromId($class, $value);
        }
        if ($fld=='id'.get_class($this).'Type') {
          $result['idType']=$value;
          $result['nameType']=$result['name'.$class];
        }
      } else if ($dataType=='date') {
        $result[$fld]=htmlFormatDate($value);
      } else if ($dataType=='time') {
        $result[$fld]=htmlFormatTime($value);
      } else if ($dataType=='datetime') {
        $result[$fld]=htmlFormatDateTime($value);
      } else if (substr($fld, -4, 4)=='Cost' or substr($fld, -6, 6)=='Amount' or $fld=='amount' or $fld=='price') {
        $tmpVal=htmlDisplayCurrency($value);
        if (!$outputHtml) $tmpVal=str_replace('&nbsp;', ' ', $tmpVal);
        $result[$fld]=$tmpVal;
      } else if ($dataType=="numeric" or $dataType=="decimal") {
        $tmpVal=htmlDisplayNumericWithoutTrailingZeros($value);
        if (!$outputHtml) $tmpVal=str_replace('&nbsp;', ' ', $tmpVal);
        $result[$fld]=$tmpVal;
      } else if ($outputHtml) {
        $result[$fld]=htmlEncode($value,'html');
      } 
    }
    if (property_exists($this, 'refType') and property_exists($this, 'refId') and !property_exists($this, 'refName')) {
      $result['refName']=self::getRefName($this->refType, $this->refId);
      $result['refTypeName']=i18n($this->refType);
    }
    if (property_exists($this, 'originType') and property_exists($this, 'originId')) {
      $result['originName']=self::getRefName($this->originType, $this->originId);
      $result['originTypeName']=i18n($this->originType);
    }
    if (get_class($this)=='Attachment') {
      if ($this->link) $this->fileName=$this->link;
    }
    if (property_exists($this, 'ref1Type') and property_exists($this, 'ref1Id') and property_exists($this, 'ref2Type') and property_exists($this, 'ref2Id')) {
      $result['ref1Name']=self::getRefName($this->ref1Type, $this->ref1Id);
      $result['ref2Name']=self::getRefName($this->ref2Type, $this->ref2Id);
    if ($parent and get_class($parent)==$this->ref1Type and $parent->id==$this->ref1Id and !property_exists($this,'refType') and !property_exists($this, 'refName')) {
        $result['refType']=$this->ref2Type;
        $result['refId']=$this->ref2Id;
        $result['refName']=$result['ref2Name'];
        $result['refTypeName']=i18n($this->ref2Type);
        $result['refStatus']=self::getRefStatus($this->ref2Type, $this->ref2Id);
        $result['refResponsible']=self::getRefResponsible($this->ref2Type, $this->ref2Id);
        $result['refInitialDueDate']=self::getRefInitialDueDate($this->ref2Type, $this->ref2Id);
        $result['refActualDueDate']=self::getRefActualDueDate($this->ref2Type, $this->ref2Id);
        $result['refDate']=self::getRefDate($this->ref2Type, $this->ref2Id);
        $result['refDescription']=self::getRefDescription($this->ref2Type, $this->ref2Id);
        if (property_exists($this->ref2Type, 'contactFunction')) $result['contactFunction']=SqlList::getFieldFromId($this->ref2Type, $this->ref2Id,'contactFunction');
        if (property_exists($this->ref2Type, 'email')) $result['email']=SqlList::getFieldFromId($this->ref2Type, $this->ref2Id,'email');
        if (property_exists($this->ref2Type, 'phone')) $result['phone']=SqlList::getFieldFromId($this->ref2Type, $this->ref2Id,'phone');
      } else {
        $result['refType']=$this->ref1Type;
        $result['refId']=$this->ref1Id;
        $result['refName']=$result['ref1Name'];
        $result['refTypeName']=i18n($this->ref1Type);
        $result['refStatus']=self::getRefStatus($this->ref1Type, $this->ref1Id);
        $result['refResponsible']=self::getRefResponsible($this->ref1Type, $this->ref1Id);
        $result['refInitialDueDate']=self::getRefInitialDueDate($this->ref1Type, $this->ref1Id);
        $result['refActualDueDate']=self::getRefActualDueDate($this->ref1Type, $this->ref1Id);
        $result['refDate']=self::getRefDate($this->ref1Type, $this->ref1Id);
        $result['refDescription']=self::getRefDescription($this->ref1Type, $this->ref1Id);
        if (property_exists($this->ref1Type, 'contactFunction')) $result['contactFunction']=SqlList::getFieldFromId($this->ref1Type, $this->ref1Id,'contactFunction');
        if (property_exists($this->ref1Type, 'email')) $result['email']=SqlList::getFieldFromId($this->ref1Type, $this->ref1Id,'email');
        if (property_exists($this->ref1Type, 'phone')) $result['phone']=SqlList::getFieldFromId($this->ref1Type, $this->ref1Id,'phone');
      }
    }
    if (get_class($this)=='Project' and !$parent) {
      $aff=new Affectation();
      $affList=$aff->getSqlElementsFromCriteria(array('idProject'=>$this->id,'idle'=>'0'));
      $sub=SqlElement::toArrayList($affList,$this,$outputHtml);
      $result['affectation']=$sub;
    }
    return $result;
  }

  private static $_lastRefObject=null;
  public static function getRefName($refType,$refId) {
    return self::getRefField($refType, $refId, 'name');
  }
  public static function getRefStatus($refType,$refId) {
    $idStatus=self::getRefField($refType, $refId, 'idStatus');
    return ($idStatus)?SqlList::getNameFromId('Status',$idStatus):'';
  }
  public static function getRefDescription($refType,$refId) {
    $desc=self::getRefField($refType, $refId, 'description');
    $text=new Html2Text($desc);
    return $text->getText();
  }
  public static function getRefResponsible($refType,$refId) {
    $idResource=self::getRefField($refType, $refId, 'idResource');
    return ($idResource)?SqlList::getNameFromId('Affectable',$idResource):'';
  }
  public static function getRefInitialDueDate($refType,$refId) {
    $refObj=self::getRefObj($refType,$refId);
    if (property_exists($refObj, 'initialDueDate')) {
      return htmlFormatDate($refObj->initialDueDate);
    } else if (property_exists($refObj, 'initialDueDateTime')) {
      return htmlFormatDate($refObj->initialDueDateTime,true);
    } else if (property_exists($refObj, 'initialEndDate')) {
      return htmlFormatDate($refObj->initialEndDate,true);  
    } else {
      return '';
    }
  }
  public static function getRefActualDueDate($refType,$refId) {
    $refObj=self::getRefObj($refType,$refId);
    if (property_exists($refObj, 'actualDueDate')) {
      return htmlFormatDate($refObj->actualDueDate);
    } else if (property_exists($refObj, 'actualDueDateTime')) {
      return htmlFormatDate($refObj->actualDueDateTime,true);
    } else if (property_exists($refObj, 'actualEndDate')) {
        return htmlFormatDate($refObj->actualEndDate,true);
      
    } else {
      return '';
    }
  }
  public static function getRefDate($refType,$refId) {
    $initial=self::getRefActualDueDate($refType,$refId);
    if ($initial) return $initial;
    $actual=self::getRefInitialDueDate($refType,$refId);
    if ($actual) return $actual;
    $decision=self::getRefField($refType, $refId, 'decisionDate');
    if ($decision) return htmlFormatDate($decision);
    return '';
  }
  
  public static function getRefObj($refType,$refId) {
    if ($refType and $refId and SqlElement::class_exists($refType)) {
      if (self::$_lastRefObject and is_object(self::$_lastRefObject)
      and get_class(self::$_lastRefObject)==$refType and self::$_lastRefObject->id==$refId) {
        $refObj=self::$_lastRefObject;
      } else {
        $refObj=new $refType($refId);
        self::$_lastRefObject=$refObj;
      }
    } else {
      if ($refType and ! SqlElement::class_exists($refType)) {
        traceLog("SqlElement::getRefObj() : '$refType' does not reference a valid object class");
        //debugPrintTraceStack();
      }
      $refObj=new stdClass();
    }
    return $refObj;
  }
  public static function getRefField($refType,$refId,$field) {
    if ($field=='name' and ($refType=='Resource' or $refType=='User' or $refType=='Contact')) $refType='Affectable';
    if (!$refType or !$field) return '';  
    $refObj=self::getRefObj($refType,$refId);
    if (property_exists($refObj, $field)) {
      return $refObj->$field;
    } else {
      return '';
    }
  }
  //public function setAttributes() {
  //  DO NOT SET GLOBAL DEFINITION AS SIGNATURE DEPENDS ON ITEM
  //}
  public function getFirstStatus($type) {
    // Up to V6.5, first status is always first in the list, whatever the workflow
    $stList=SqlList::getList('Status');
    $first=reset($stList);
    $status = key($stList); // first status always first in the list
    return $status;
  }
  public static function mergeAttributesArrays($selfArray,$parentArray){
    foreach($parentArray as $key=>$val) {
      if (!isset($selfArray[$key])) {
        $selfArray[$key]=$val;
      } else {
        $selfArray[$key].=','.$val;
      }
    }
    //return self::$_fieldsAttributes=array_merge_preserve_keys($selfArray,$parentArray);
    return $selfArray;
  }
  public static function traceFields($obj,$fields) {
    if (!is_array($fields)) $fields=array($fields);
    $msg="=> ".get_class($obj).' #'.$obj->id;
    foreach ($fields as $fld) {
      $msg.=", $fld=";
      if (property_exists($obj, $fld)) {
        $msg.=$obj->$fld;
      } else {
        $msg.='#not a field#';
      }
    }
    debugTraceLog($msg);
  }
  //florent #4442
  public  function searchLastAttachmentMailable (){
    $allAttach=searchAllAttachmentMailable(get_class($this),$this->id);
    $lstAttach=$allAttach[0];
    if(!empty($lstAttach))$att=$lstAttach[0];
    if(isset($att)){
      if($att->idPrivacy==1){
        return $name=$att->id.'_file/'.(($att->fileSize!='')?$att->fileSize:0);
      }
    }else {
      return;
    }
  }
  public function searchAllAttachmentsMailable($maxSizeAttachment){
    $allAttach=searchAllAttachmentMailable(get_class($this),$this->id);
    $lstAttach=$allAttach[0];
    $attachments=array();
    $size=0;
    $c=0;
    $message='Ok';
    foreach ($lstAttach as $att){
      $c++;
      if($att->idPrivacy==1){
        $size+=($att->fileSize!='')?$att->fileSize:0;
        if($size > $maxSizeAttachment ){
          break;
        }else{
          $attachments[]= array($att->id,'file');
        }
      }else{
        continue;
      }
    }
    if (count($lstAttach)!=$c){
      $message='Fail';
    }
    return array('attachments'=>$attachments,'result'=>$message);
  }
  //
}

  
?>
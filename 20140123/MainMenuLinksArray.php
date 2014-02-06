<?php

/* Last MOD: Tamelo Douglas
 * 21/06/2013..
 * New Header array Options, external links..
 * Major Shift..
 * Menus - Wazi new menus */


/* The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array('orders', 'AR', 'AP', 'PO', 'stock', 'manuf', 'GL', 'CRM', 'HRM', 'HQ', 'system', 'MD');
/* The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
/*  SALES ORDERS LINE144 , DEBTORS LINE292 , CREDITORS LINE429 , PURCH ORDERS LINE509, ,STOCK LINE583 ,MANUF LINE777, GL LINE1226, XTRAS LINE1362, HEADOFF LINE1532 ,SETUP LINE922 */
$ModuleList = array(_('Sales'), _('Debtors'), _('Creditors'), _('Purchases'), _('Inventory'), _('Manufacture'), _('G Ledger'), _('CRM'), _('HRM'), _('Head Office'), _('Setup'), _('MD'));



/* left side display for orders */
$MenuItems['orders']['Transactions']['Caption'] = array(_('Counter Sales (Disallow below cost'),
    _('Tender Outstanding Invoice'),
    _('Invoice For Account Customers'),
    _('Create Quotation'),
    _('Outstanding Quotations'),
    _('Create Sales Order'),
    _('Outstanding Sales Orders'),
    _('Cash Up'),
    _('Cash Up History'),
    _('Input Sales Orders Massbuild'),
    _('Loyal Customers Quotations  - BETA'),
    _('Massbuild SalesOrder And Invoices(Reasons)'));

$MenuItems['orders']['Transactions']['URL'] = array('/CounterSales.php',
    '/OpenInvoice.php',
    '/SelectOrderItems.php?NewOrder=Yes',
    '/SelectOrderItemsQuote.php?NewOrder=Yes',
    '/OutstandingQuotations.php',
    '/SelectOrderItemsSalesOrder.php?NewOrder=Yes',
    '/SelectSalesOrder.php?',
    '/Cashup.php',
    '/Cashuphistory.php', '/SelectOrderItemsMB.php',
    '/SelectOrderItemsLoyalCustomeQuote.php?NewOrder=Yes',
    '/SelectSalesOrderBuilders.php'
);
/* header Options */
$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][0]] = ('Cash - Sales');
//$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][1]] = ('Cash - Sales');
$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][2]] = ('Account Customer - Sales');
$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][3]] = ('Quotations');
//$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][4]] = ('Quotations');
$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][5]] = ('Sales Orders');
//$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][6]] = ('Sales Orders');
$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][7]] = ('Cash Up');
//$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][8]] = ('Cash Up');
$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][9]] = ('Software - Beta or Legacy code');
//$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][10]] = ('Software - Beta or Legacy code');
//$MenuItems['Header']['Transactions'][$MenuItems['orders']['Transactions']['URL'][11]] = ('Software - Beta or Legacy code');
/* * */
$MenuItems['orders']['Reports']['Caption'] = array(_('Order Inquiry'),
    _('Order Status Reports (Print)'),
    _('Orders Invoiced Reports'),
    _('Order Delivery Differences Report'),
    _('Delivery In Full On Time (DIFOT) Report'),
    _('Clients on my rep code'),
    _('My 12 months Sales Transactions'),
    _('My Sales Transactions'),
    _('Walk In Customers Form'));


$MenuItems['orders']['Reports']['URL'] = array('/SelectCompletedOrderz.php',
    '/PDFOrderStatusz.php',
    '/PDFOrdersInvoicedz.php',
    '/PDFDeliveryDifferencesz.php',
    '/PDFDIFOT.php',
    '/RepClients.php',
    '/MySalesGraphTransactions.php',
    '/MySalesTransactions.php',
    '/WalkInCustomers.php');

$MenuItems['Header']['Reports'][$MenuItems['orders']['Reports']['URL'][5]] = ('Reports - Sales');


$MenuItems['orders']['Maintenance']['Caption'] = array(_('Clear Outstanding Sales Orders/Quotations'),
    _('Loyal Customers'));


$MenuItems['orders']['Maintenance']['URL'] = array('/SalesOrderClearz.php',
    '/BranchLoyalClients.php');



/* * ****************************************************************************************************************************************** */

$MenuItems['AR']['Transactions']['Caption'] = array(_('Select Order to Invoice'),
    _('Create A Credit Note'),
    _('Credit Note'),
    _('Enter Receipts'),
    _('Allocate Receipts or Credit Notes'));

$MenuItems['AR']['Transactions']['URL'] = array('/SelectSalesOrder.php',
    '/SelectCreditItems.php?NewCredit=Yes',
    '/CreditNote.php?NewCredit=Yes',
    '/CustomerReceipt.php?NewReceipt=Yes',
    '/CustomerAllocations.php');

$MenuItems['AR']['Reports']['Caption'] = array(_('Customer Transaction Inquiries'),
    _('Loyal Customer Transaction Inquiries'),
    _('Where Allocated Inquiry'),
    _('Print Invoices or Credit Notes'),
    _('Sales Analysis Reports'),
    _('Aged Customer Balances/Overdues Report'),
    _('Transaction Inquiries'),
    _('Re-Print A Deposit Listing'),
    _('Debtor Balances At A Prior Month End'),
    _('Customer Listing By Area/Salesperson'),
    _('Branch Inventory History'));

if ($_SESSION['InvoicePortraitFormat'] == 0) {
    $PrintInvoicesOrCreditNotesScript = '/PrintCustTrans.php';
} else {
    $PrintInvoicesOrCreditNotesScript = '/PrintCustTransPortrait.php';
}

$MenuItems['AR']['Reports']['URL'] = array('/SelectCustomer.php',
    '/SelectLoyalCustomer.php',
    '/CustWhereAlloc.php',
    $PrintInvoicesOrCreditNotesScript,
    '/SalesAnalRepts.php',
    '/AgedDebtors.php',
    '/CustomerTransInquiry.php',
    '/PDFBankingSummary.php',
    '/DebtorsAtPeriodEnd.php',
    '/PDFCustomerList.php',
    '/LocationInventoryBranchHistory.php');

$MenuItems['Header']['Reports'][$MenuItems['AR']['Reports']['URL'][10]] = _('Reports - Debtors');

$MenuItems['AR']['Maintenance']['Caption'] = array(_('Add Customer'),
    _('Select Customer'));

$MenuItems['AR']['Maintenance']['URL'] = array('/Customers.php',
    '/SelectCustomer.php');

$MenuItems['AP']['Transactions']['Caption'] = array(_('Select Supplier'),
    _('Supplier Allocations'));

/* * ***********************Above Done..******************* */

$MenuItems['AP']['Transactions']['URL'] = array('/SelectSupplier.php',
    '/SupplierAllocations.php');

$MenuItems['AP']['Reports']['Caption'] = array(_('Aged Supplier Report'),
    _('Payment Run Report'),
    _('Print Supplier Statements'),
    _('Outstanding GRNs Report'),
    _('Supplier Balances At A Prior Month End'));


$MenuItems['AP']['Reports']['URL'] = array('/AgedSuppliers.php',
    '/SuppPaymentRun.php',
    '/PrintSupplierStatements.php',
    '/OutstandingGRNs.php',
    '/SupplierBalsAtPeriodEnd.php');


$MenuItems['AP']['Maintenance']['Caption'] = array(_('Add Supplier'),
    _('Maintain Factor Companies'));
$MenuItems['AP']['Maintenance']['URL'] = array('/Suppliers.php',
    '/Factors.php');
/* Above done!!! */

$MenuItems['PO']['Transactions']['Caption'] = array(_('Purchase Orders'),
    _('Add A Purchase Order'),
    _('Shipment Entry'),
    _('Select A Shipment'));

$MenuItems['PO']['Transactions']['URL'] = array('/PO_SelectOSPurchOrder.php',
    '/PO_Header.php?NewOrder=Yes',
    '/SelectSupplier.php',
    '/Shipt_Select.php');


$MenuItems['PO']['Reports']['Caption'] = array(_('Purchase Order Inquiry'),
    _('Purchase Order Detail Or Summary Inquiries'),
    _('Print Last Received Purchase-Order Report'));

$MenuItems['PO']['Reports']['URL'] = array('/PO_SelectPurchOrder.php',
    '/POReport.php',
    '/LastGrn.php');

$MenuItems['Header']['Reports'][$MenuItems['PO']['Reports']['URL'][2]] = _('Reports - Purchase Orders');
$MenuItems['PO']['Maintenance']['Caption'] = array(_('Maintain Purchasing Data'));

$MenuItems['PO']['Maintenance']['URL'] = array('/PurchData.php');



/* Above done */
$MenuItems['stock']['Transactions']['Caption'] = array(_('Receive Purchase Orders'),
    _('Inventory Adjustments'),
    _('Reverse Goods Received'),
    _('Branch Stock Request'),
    _('Outstanding Stock Requests'),
    _('Consolidated Stock Requests'),
    _('Single Item WH Tfr use Bulk below'),
    _('Bulk Item Warehouse Transfers'),
    _('Stock Take'),
    _('Stock Take Report'));

$MenuItems['stock']['Transactions']['URL'] = array('/PO_SelectOSPurchOrder.php',
    '/StockAdjustments.php',
    '/ReverseGRN.php',
    '/StockLocTransferIntern.php?Newitem=yes',
    '/StockRequest.php?Newitem=yes',
    '/StockRequestConsolidated.php',
    '/StockTransfersWarehouse.php',
    '/StockWarehouseTfrBulk.php?Newitem=yes',
    '/StockVariance.php',
    '/StockTakeReport.php');


$MenuItems['Header']['Transactions'][$MenuItems['stock']['Transactions']['URL'][3]] = _('Branch Location Transfer');
$MenuItems['Header']['Transactions'][$MenuItems['stock']['Transactions']['URL'][6]] = _('Inter Warehouse Transfer - Cape Branch only');
$MenuItems['Header']['Transactions'][$MenuItems['stock']['Transactions']['URL'][8]] = _('Stock Take Menu');
//$MenuItems['Header']['Transactions'][$MenuItems['system']['Reports']['URL'][14]] = _('Users / Sales Settings');
/* above done */
$MenuItems['stock']['Reports']['Caption'] = array(_('Serial Item Research Tool'),
    _('Inventory Item Movements'),
    _('Inventory Item Status'),
    _('Inventory Item Usage'),
    _('Inventory Quantities'),
    _('Reorder Level'),
    _('Inventory Valuation Report'),
    _('Inventory Planning Report'),
    _('Inventory Planning Based On Preferred Supplier Data'),
    _('Make Inventory Quantities CSV'),
    _('All Inventory Movements By Location/Date'),
    _('List Inventory Status By Location/Category'),
    _('Historical Stock Quantity By Location/Category'),
    _('List Negative Stocks'),
    _('Inventory Stock Transfer Docs Print'),
    _('Warehouse Stock Transfer Docs Print'));


$MenuItems['stock']['Reports']['URL'] = array('/StockSerialItemResearch.php',
    '/StockMovements.php',
    '/StockStatus.php',
    '/StockUsage.php',
    '/InventoryQuantities.php',
    '/ReorderLevel.php',
    '/BranchInventoryValuation.php',
    '/BranchInventoryPlanning.php',
    '/InventoryPlanningPrefSupplier.php',
    '/StockQties_csv.php',
    '/StockLocMovements.php',
    '/StockLocStatus.php',
    '/StockQuantityByDate.php',
    '/PDFStockNegatives.php',
    '/PrintStockRequest.php',
    '/WarehouseTransfersPrint.php');

$MenuItems['Header']['Reports'][$MenuItems['stock']['Reports']['URL'][14]] = _('Reports - Stock');
$MenuItems['stock']['Maintenance']['Caption'] = array(_('Add A New Item'),
    _('Select An Item'),
    _('Sales Category Maintenance'));


$MenuItems['stock']['Maintenance']['URL'] = array('/Stocks.php',
    '/SelectProduct.php',
    '/SalesCategories.php');

/* Above done... */
$MenuItems['manuf']['Transactions']['Caption'] = array(_('Work Order Entry'),
    _('Select A Work Order'),
    _('Item Manufacturing'),
    _('Item Dis Assembly'));

$MenuItems['manuf']['Transactions']['URL'] = array('/WorkOrderEntry.php',
    '/SelectWorkOrder.php',
    '/BomManufacturing.php?Newbom=Yes',
    '/BomDeManufacturing.php');

$MenuItems['manuf']['Reports']['Caption'] = array(_('Select A Work Order'),
    _('Costed Bill Of Material Inquiry'),
    _('Where Used Inquiry'),
    _('Indented Bill Of Material Listing'),
    _('List Components Required'),
    _('Indented Where Used Listing'),
    _('MRP'),
    _('MRP Shortages'),
    _('MRP Suggested Purchase Orders'),
    _('MRP Reschedules Required'));


$MenuItems['manuf']['Reports']['URL'] = array('/SelectWorkOrder.php',
    '/BOMInquiry.php',
    '/WhereUsedInquiry.php',
    '/BOMIndented.php',
    '/BOMExtendedQty.php',
    '/BOMIndentedReverse.php',
    '/MRPReport.php',
    '/MRPShortages.php',
    '/MRPPlannedPurchaseOrders.php',
    '/MRPReschedules.php');


$MenuItems['manuf']['Maintenance']['Caption'] = array(_('Work Centre'),
    _('Bills Of Material'),
    _('Master Schedule'),
    _('Auto Create Master Schedule'),
    _('MRP Calculation'));


$MenuItems['manuf']['Maintenance']['URL'] = array('/WorkCentres.php',
    '/BOMs.php',
    '/MRPDemands.php',
    '/MRPCreateDemands.php',
    '/MRP.php');


/* Above Done */
$MenuItems['GL']['Transactions']['Caption'] = array(_('Bank Account Payments Entry(Original)'),
    _('Cash Book Payment'),
    _('Bank Account Receipts Entry'),
    _('Journal Entry'),
    _('Bank Account Payments Matching'),
    _('Bank Account Receipts Matching'),
    _('Cash Up Reconciliation'));

$MenuItems['GL']['Transactions']['URL'] = array('/Payments.php?NewPayment=Yes',
    '/CashBookPayment.php?NewPayment=Yes',
    '/CustomerReceipt.php?NewReceipt=Yes',
    '/GLJournal.php?NewJournal=Yes',
    '/BankMatching.php?Type=Payments',
    '/BankMatching.php?Type=Receipts',
    '/CashUpRecon.php');

$MenuItems['GL']['Reports']['Caption'] = array(_('Trial Balance'),
    _('Account Inquiry'),
    _('Bank Account Reconciliation Statement'),
    _('Cheque Payments Listing'),
    _('Profit and Loss Statement'),
    _('Balance Sheet'),
    _('Tag Reports'),
    _('Tax Reports'),
    _('Daily Bank Transactions'),
    _('All Cash Sales Report'),
    _('Outstanding Cash Ups'));


$MenuItems['GL']['Reports']['URL'] = array('/GLTrialBalance.php',
    '/SelectGLAccount.php',
    '/BankReconciliation.php',
    '/PDFChequeListing.php',
    '/GLProfit_Loss.php',
    '/GLBalanceSheet.php',
    '/GLTagProfit_Loss.php',
    '/Tax.php',
    '/DailyBankTransactions.php',
    '/AllCashReport.php',
    '/CashUpReport.php');


$MenuItems['GL']['Maintenance']['Caption'] = array(_('GL Account'),
    _('GL Account List'),
    _('Account Groups'),
    _('Account Sections'),
    _('GL Tags'));

$MenuItems['GL']['Maintenance']['URL'] = array('/GLAccountInquiry.php',
    '/GLAccounts.php',
    '/AccountGroups.php',
    '/AccountSections.php',
    '/GLTags.php');

/* Above Done */


$MenuItems['system']['Transactions']['Caption'] = array(_('Company Preferences'),
    _('Configuration Settings'),
    _('Bank Accounts'),
    _('Currency Maintenance'),
    _('Tax Authorities and Rates Maintenance'),
    _('Tax Group Maintenance'),
    _('Dispatch Tax Province Maintenance'),
    _('Tax Category Maintenance'),
    _('List Periods Defined<font size=1>(Periods are automatically maintained)</font>'),
    _('Sales GL Interface Postings'),
    _('COGS GL Interface Postings'),
    _('Geocode Setup'),
    _('Report Builder Tool'),
    _('<font color=blue>What Security Level per Program</font>'),
    _('User Maintenance'),
    _('Role Permissions'),
    _('Sales People'));




$MenuItems['system']['Transactions']['URL'] = array('/CompanyPreferences.php',
    '/SystemParameters.php',
    '/BankAccounts.php',
    '/Currencies.php',
    '/TaxAuthorities.php',
    '/TaxGroups.php',
    '/TaxProvinces.php',
    '/TaxCategories.php',
    '/PeriodsInquiry.php',
    '/SalesGLPostings.php',
    '/COGSGLPostings.php',
    '/GeocodeSetup.php',
    '/reportwriter/admin/ReportCreator.php',
    '/PageSecurity.php',
    '/WWW_Users.php',
    '/WWW_Access.php',
    '/SalesPeople.php');


$MenuItems['Header']['Transactions'][$MenuItems['system']['Transactions']['URL'][4]] = _('Tax G/L Settings - Do not Adjust');
$MenuItems['Header']['Transactions'][$MenuItems['system']['Transactions']['URL'][14]] = _('Users / Sales Settings');

$MenuItems['system']['Reports']['Caption'] = array(_('<font color=blue>Invoice, Receipt etc. Number Maintenance</font>'),
    _('Sales Areas'),
    _('Customer Groupings'),
    _('Types of Credit Status'),
    _('Payment Terms'),
    _('Set Purchase Order Authorisation levels'),
    _('<font color=blue>Cash Sales Tender Types</font>'),
    _('Shippers'),
    _('Freight Costs Maintenance'),
    _('Discount Matrix'),
    _('Debtor Discounts Settings'),
    _('Price Lists'),
    _('Message of the day'),
    _('Whom to get Alerts'),
    _('First Contact Table'),
    _('Comments and quotations'),
    _('<font color=blue>Message of the day</font>'));

$MenuItems['system']['Reports']['URL'] = array('/NextNumber.php',
    '/Areas.php',
    '/CustomerTypes.php',
    '/CreditStatus.php',
    '/PaymentTerms.php',
    '/PO_AuthorisationLevels.php',
    '/PaymentMethods.php',
    '/Shippers.php',
    '/FreightCosts.php',
    '/DiscountMatrix.php',
    '/DebtorDiscountsSettings.php',
    '/SalesTypes.php',
    '/MessagOfDay.php',
    '/Alerts.php',
    '/SystemCustomerContacts.php',
    '/SystemComments.php',
    '/MessagOfDay.php');
$MenuItems['Header']['Reports'][$MenuItems['system']['Reports']['URL'][11]] = _('Minor Local Settings');
//$MenuItems['Header']['Transactions'][$MenuItems['system']['Reports']['URL'][14]] = _('Users / Sales Settings');

$MenuItems['system']['Maintenance']['Caption'] = array(_('Inventory Categories Maintenance'),
    _('Inventory Locations Maintenance'),
    _('Discount Category Maintenance'),
    _('Units of Measure'),
    _('MRP Available Production Days'),
    _('MRP Demand Types'),
    _('<font color=red>Run Daily sales cron</font>'));
//_('Maintain Internal Stock Categories to User Roles') );

$MenuItems['system']['Maintenance']['URL'] = array('/StockCategories.php',
    '/Locations.php',
    '/DiscountCategories.php',
    '/UnitsOfMeasure.php',
    '/MRPCalendar.php',
    '/MRPDemandTypes.php',
    '/cron/salescron.php');
//'/InternalStockCategoriesByRole.php' );

$MenuItems['Header']['Maintenance'][$MenuItems['system']['Maintenance']['URL'][0]] = _('Stock Setup Settings');
$MenuItems['Header']['Maintenance'][$MenuItems['system']['Maintenance']['URL'][6]] = _('For System Administrator Only');
//$MenuItems['Header']['Transactions'][$MenuItems['system']['Reports']['URL'][14]] = _('Users / Sales Settings');
/* * *********************************************************************************************************************************************************** */
$MenuItems['HQ']['Transactions']['Caption'] = array(_('<font color=blue>Modify Retail/non-retail Items</font>'),
    _('<font color=blue>Allow Negative Stock/Invoicing</font> '),
    _('<font color=blue>Stock Take</font>'),
    _('<font color=blue>Stock Take Report</font>'),
    _('All Stock Movements'),
    _('Warehouse to Warehouse Stock Movements'),
    _('Inventory Planning According to Sales Report 2012'),
    _('Inventory Transfers from Warehouse Report BETA'),
    _('Items with no movements..'),
    _('Last Ten(10) received goods'),
    _('Sales/Transfers/Manufacturing Report'),
    _('Inventory Stock Status (CSV) Report'),
    _('Inventory Planning According to Sales (24 Mnths  CSV) Report'),
    _('All purchase items(Received items)'),
    
    _('Inventory Valuation Report'),
    _('Show stock items at zero value'),
    _('Show all phantom stock'),
    _('Add or Update Prices Based On Costs'),
    _('<font color=blue>Change List prices according to Cost</font>'),
    _('<font color=blue>Print Price Lists</font>'),
    _('<font color=blue>Print Price Lists with cost</font>'),
    _('<font color=blue>eCommerce Price List-Durostore</font>'),
    _('<font color=blue>Multiple Price Insert</font>'),
    _('Manufacturing Report')
);

$MenuItems['HQ']['Transactions']['URL'] = array('/Z_SetRetailProducts.php',
    '/AdminAllowNegativeIn.php',
    '/StockVariance.php',
    '/StockTakeReport.php',
    '/AllStockMovements.php',
    '/warehouseTowarehouseTrfs.php',
    '/BranchInventoryPlanningHistory.php',
    '/InventoryPlanningTfrs.php',
    '/BranchInventoryZeroHistory.php',
    '/LastGrn.php',
    '/PerLocBranchInventoryPlanningHistory.php',
    '/InventoryStatusReportcsv.php',
    '/BranchInventoryPlanningHistoryCsv.php',
    '/PurchaseHistoryTemp.php',
    
    '/InventoryValuation.php',
    '/StockValueZero.php',
    '/PhantomStock.php',
    '/PricesBasedOnMarkUp.php',
    '/PricesByCost.php',
    '/PDFPriceList.php',
    '/PDFPriceListCost.php',
    '/PDFPriceListEcom.php',
    '/PricesUpdates.php',
    '/ManufactureHistory.php',
);
$MenuItems['Header']['Transactions'][$MenuItems['HQ']['Transactions']['URL'][0]] = _('Stock Head Offfice');
$MenuItems['Header']['Transactions'][$MenuItems['HQ']['Transactions']['URL'][14]] = _('Stock Valuation - National');
$MenuItems['Header']['Transactions'][$MenuItems['HQ']['Transactions']['URL'][18]] = _('Price Lists and Modifications');
$MenuItems['Header']['Transactions'][$MenuItems['HQ']['Transactions']['URL'][24]] = _('Manufacturing');


$MenuItems['HQ']['Reports']['Caption'] = array(_('Customer Transactions Inquries'),
    _('All Locations Transactions Inquries'),
    _('Debtor Inquries - All locations'),
    _('All Customer Age Analysis'),
    _('Customer Purchase History(Per Branch)'),
    _('<font color=blue>Allocated Reps Report</font>'),
    _('<font color=blue>Type of Debtors</font>'),
    _('<font color=blue>Debtors Contact Details</font>'),
    _('Sort All Customers By Type'),
    _('All Clients And Rep Codes'),
    _('All loyal Customers'),
    _('<font color=blue> Print Statements</font>'),
    _('<font color=blue>Export Statements to CSV</font>'),
    _('<font color=blue>Customers To Receive Statements</font>'),
    _('<font color=blue>Save Statements to Server</font>'),
    _('<font color=blue>View Saved Statements/Server Directories</font>'),
    _('<font color=blue>Send Statements</font>'),
    _('<font color=blue>Sent Emails Report</font>'),
    _('<font color=blue>Re-Send Statements not in resend module</font>'),
    _('<font color=blue>Customers with no email set</font>'),
    _('<font color=blue>Sales Order Detail Or Summary Inquiries</font>'),
    _('Sales below GP Margin'),
    _('Sales above GP Margin'),
    _('Authorised: Below Cost Items Report'),
    _('Debtors Inventory Planning History Report'),
    _('Branch Inventory Planning History Report')
);

$MenuItems['HQ']['Reports']['URL'] = array('/CustomerTransInquiryAll.php',
    '/CustomerTransInquiryx.php',
    '/CustomerInquiryAll.php',
    '/AgedDebtorsAll.php',
    '/DebtorPurchaseHistory.php',
    '/reportwriter/ReportMaker.php?action=go&reportid=18',
    '/reportwriter/ReportMaker.php?action=go&reportid=24',
    '/ShowCustContacts.php',
    '/AllCustomersByType.php',
    '/AllRepClients.php',
    '/AllRepLoyalClients.php',
    '/PrintCustStatements.php',
    '/StatementsCsvExport.php',
    '/CustomersToReceiveStatements.php',
    '/SaveCustStatements.php',
    '/listdirectory.php',
    '/SendStatements.php',
    '/SentEmailsReport.php',
    '/SelectResendStatements.php',
    '/NoEmailContact.php',
    '/SalesInquiry.php',
    '/PDFLowGP.php',
    '/PDFHighGP.php',
    '/PriceAuthorizationReport.php',
    '/InventoryDebtorHistory.php',
    '/InventoryBranchHistory.php');





$MenuItems['Header']['Reports'][$MenuItems['HQ']['Reports']['URL'][0]] = _('Debtors for Group');

$MenuItems['Header']['Reports'][$MenuItems['HQ']['Reports']['URL'][10]] = _('Loyal Customers (All locations)');
$MenuItems['Header']['Reports'][$MenuItems['HQ']['Reports']['URL'][11]] = _('Statements');
$MenuItems['Header']['Reports'][$MenuItems['HQ']['Reports']['URL'][20]] = _('Sales for Group');
$MenuItems['Header']['Reports'][$MenuItems['HQ']['Reports']['URL'][24]] = _('Inventory Movements for Debtor or Branch');


$MenuItems['HQ']['Maintenance']['Caption'] = array(_('All Paradyne Sales Orders'),
    _('All Branches Sales Orders'),
    _('Sales Orders Item Quantities'),
    _('All Branches Flagged Quotations'),
    _('All Branches Flagged Sales Orders'),
    _('Complete Check : Shows on screen'),
    _('View Database and Php Version '),
    _('Un Matched GL Entries select Period'),
    _('View Audit Trail'),
    _('Upload excel And Create XML'),
    _('Process RPL'),
    _('Delete RPLs(XML-FILE)'),
    _('Load Items to Server'),
    _('Upload Items to the database'),
    _('Cash Up'),
    _('Reset/reverse Cash-Up'),
    _('Edit SalesOrder Details/Invoice Header'),
    _('Message Of The Day'),
    _('Multiple Items Updates')
);

$MenuItems['HQ']['Maintenance']['URL'] = array('/SelectSalesOrderPar.php',
    '/SelectSalesOrderAll.php',
    '/SalesOrderAllQuantites.php',
    '/AdminOutstandingQuotations.php',
    '/AdminFlagOutstandingSalesOrder.php',
    '/ModuleCheckShow.php',
    '/SystemCheck.php',
    '/Z_CheckGLTransBalChoose.php',
    '/AuditTrail.php',
    '/xmlupload.php',
    '/DebtorPurchaseOrders.php',
    '/Delete_rplxml.php',
    '/xmluploadstock.php',
    '/Z_ImportxmlStocks.php',
    '/Z_SuspendedCashUp.php',
    '/Z_CashUp.php',
    '/Z_SalesOrderEdit.php',
    '/MessagOfDay.php',
    '/MultiItemUpdates.php'
);

$MenuItems['Header']['Maintenance'][$MenuItems['HQ']['Maintenance']['URL'][0]] = _('Sales Orders for Group');
$MenuItems['Header']['Maintenance'][$MenuItems['HQ']['Maintenance']['URL'][5]] = _('System Audit Checks');
$MenuItems['Header']['Maintenance'][$MenuItems['HQ']['Maintenance']['URL'][9]] = _('RPL - XML');
$MenuItems['Header']['Maintenance'][$MenuItems['HQ']['Maintenance']['URL'][12]] = _('LOAD New ITEMS - XML');
$MenuItems['Header']['Maintenance'][$MenuItems['HQ']['Maintenance']['URL'][14]] = _('ADMIN - CASH UPs AND (SALES ORDERS | INVOICE)');
$MenuItems['Header']['Maintenance'][$MenuItems['HQ']['Maintenance']['URL'][17]] = _('ADMIN - Minor Changes');


/* * **************************************************CRM************************************************************************************* */


$MenuItems['CRM']['Transactions']['Caption'] = array(_('Login Page')
);

$MenuItems['CRM']['Transactions']['URL'] = array('http://bzto.bz');

$MenuItems['Header']['Transactions'][$MenuItems['CRM']['Transactions']['URL'][0]] = _('Bz to Bz');



$MenuItems['CRM']['Reports']['Caption'] = array(
    _('<font color=red>Lead Tracker Help</font>'),
    _('Lead Tracker - Duroplastic'),
    _('Lead Tracker - Paradyne'),
    _('Lead Tracker - Light Steel Frame'),
    _('Decorex Leads Report'),
);

/*External Links incoporated...*/
$MenuItems['CRM']['Reports']['URL'] = array('http://www.wazierp.net/wazi/help/leadtracker',
    'http://www.wazierp.net/leadtracker',
    'http://www.wazierp.net/leadtracker_paradyne',
    'http://www.wazierp.net/leadtracker_lsf',
    '/DecorexReport.php',
    '/DecorexReport-Durban2013.php'
);

$MenuItems['Header']['Reports'][$MenuItems['CRM']['Reports']['URL'][0]] = _('Enquiry/Lead Tracking');


$MenuItems['CRM']['Maintenance']['Caption'] = array(_('Paradyne Customer List'),_('Task Report'),_('Rating & Sales Report'));

$MenuItems['CRM']['Maintenance']['URL'] = array('/ParadyneCustomers.php','/BztobzTaskLists.php','/BztobzClientActivities.php');
$MenuItems['Header']['Maintenance'][$MenuItems['CRM']['Maintenance']['URL'][0]] = _('Reports');

/* * *****************************************************************HRM********************************************************************************************** */
$MenuItems['HRM']['Transactions']['Caption'] = array(_('Users'),
    _('Reports'),
    _('history'),
);

$MenuItems['HRM']['Transactions']['URL'] = array('//', '//', '//');

$MenuItems['Header']['Transactions'][$MenuItems['HRM']['Transactions']['URL'][0]] = _('EMPLOYEES');



$MenuItems['HRM']['Reports']['Caption'] = array(
    _('<font color=red>Reports 1</font>'),
    _('<font color=red>Reports 2</font>')
);

$MenuItems['HRM']['Reports']['URL'] = array('//',
    '//'
);

$MenuItems['Header']['Reports'][$MenuItems['HRM']['Reports']['URL'][0]] = _('REPORTS');



/* Nothing here yet** */
//$MenuItems['HRM']['Maintenance']['Caption'] = array(_(''));
//
//$MenuItems['HRM']['Maintenance']['URL'] = array('//');
//$MenuItems['Header']['Maintenance'][$MenuItems['HRM']['Maintenance']['URL'][0]] = _('Reports');


/* * *****************************************************************MD******************************************************************************************* */
/* * *****************************************************************MD******************************************************************************************* */
$MenuItems['MD']['Transactions']['Caption'] = array(_('Sales Reps Sales and GP'),
    _('Each Sales Reps with Customers'),
    _('Modify Crons Sales Reps'),
    _('Selling below Cost - Cron'),
    _('Cash vs Account per Rep'),
    _('Sales Graphs'),
    _('Sales By Category'),
    _('Top Sales Items'),
    _('<font color=purple>Daily Sales Report - Bulky</font>'),
    _('<font color=blue>Walk In Customers Report</font>'),
    _('<font color=blue>Quotation Conversions(QT->SO)</font>'),
    _('<font color=blue>Lvl1 - Sales Reports</font>'),
    _('Lvl2 - Management Sales,Margins,etc'),
    _('Lvl3 - Stock /Supplier Reports'),
    _('Lvl4 - System Admin Reports')
);

$MenuItems['MD']['Transactions']['URL'] = array('/SalesmanSalesTransactions.php',
    '/All_RepSalesTransactions.php',
    '/CronPeople.php',
    '/LowGPSetting.php',
    '/SalesTransactionsRange.php',
    '/SalesGraph.php',
    '/SalesCategoryPeriodInquiry.php',
    '/TopItems.php',
    '/phprunreports/salesdaily/menu.php?&location=' . $_SESSION['UserStockLocation'],
    '/WalkInCustomersReport.php',
    '/QuotationConversions.php',
    '/report_lvl1.php',
    '/report_lvl2.php',
    '/report_lvl3.php',
    '/report_lvl4.php'
);
$MenuItems['Header']['Transactions'][$MenuItems['MD']['Transactions']['URL'][0]] = _('Reports');
$MenuItems['Header']['Transactions'][$MenuItems['MD']['Transactions']['URL'][11]] = _('Extra Reports');
$MenuItems['MD']['Reports']['Caption'] = array(
    _('Global Replace Dangerous'),
    _('Z Index Backend'));

$MenuItems['MD']['Reports']['URL'] = array(
    '/replace/',
    '/Z_index.php');

$MenuItems['Header']['Reports'][$MenuItems['MD']['Reports']['URL'][0]] = _('System Critical');
$MenuItems['MD']['Maintenance']['Caption'] = array(_('Select Asset - NEW'));

$MenuItems['MD']['Maintenance']['URL'] = array('/SelectAsset.php');
$MenuItems['Header']['Maintenance'][$MenuItems['MD']['Maintenance']['URL'][0]] = _('Stuff in Kwamoja');
?>
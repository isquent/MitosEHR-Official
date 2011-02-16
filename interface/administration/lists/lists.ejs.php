<?php 
//******************************************************************************
// new.ejs.php
// New Patient Entry Form
// v0.0.1
// 
// Author: Ernest Rodriguez
// Modified: Gino Rivera
// 
// MitosEHR (Eletronic Health Records) 2011
//******************************************************************************

include_once("../../registry.php");

?>
<script type="text/javascript">
Ext.onReady(function(){
Ext.BLANK_IMAGE_URL = '../../library/<?php echo $GLOBALS['ext_path']; ?>/resources/images/default/s.gif';

//******************************************************************************
// ExtJS Global variables 
//******************************************************************************
var rowPos;
var currList;

//******************************************************************************
// Sanitizing Objects
// Destroy them, if already exists in the browser memory.
// This destructions must be called for all the objects that
// are rendered on the document.body 
//******************************************************************************
if ( Ext.getCmp('winList') ){ Ext.getCmp('winList').destroy(); }

// *************************************************************************************
// Structure of the message record
// creates a subclass of Ext.data.Record
//
// This should be the structure of the database table
// 
// *************************************************************************************
var ListRecord = Ext.data.Record.create([
	{name: 'list_id', 		type: 'string',	mapping: 'list_id'},
	{name: 'option_id', 	type: 'string', mapping: 'option_id'},
	{name: 'title', 		type: 'string', mapping: 'title'},
	{name: 'seq', 			type: 'string', mapping: 'seq'},
	{name: 'is_default', 	type: 'string', mapping: 'is_default'},
	{name: 'option_value', 	type: 'string', mapping: 'option_value'},
	{name: 'mapping', 		type: 'string', mapping: 'mapping'},
	{name: 'notes', 		type: 'string', mapping: 'notes'}
]);

// *************************************************************************************
// Structure, data for storeEditList
// AJAX -> component_data.ejs.php
// *************************************************************************************
var storeEditList = new Ext.data.Store({
	proxy: new Ext.data.HttpProxy({
		url: '../administration/lists/component_data.ejs.php?task=editlist'
	}),
	reader: new Ext.data.JsonReader({
		idIndex: 0,
		idProperty: 'option_id',
		totalProperty: 'results',
		root: 'row'
	},[
		{name: 'option_id', type: 'string', mapping: 'option_id'},
		{name: 'title', type: 'string', mapping: 'title'}
	])
});
storeEditList.load();
storeEditList.on('load',function(ds,records,o){ // Select the first item on the combobox
	Ext.getCmp('cmbList').setValue(records[0].data.title);
	currList = records[0].data.option_id; // Get first result for first grid data
	storeListsOption.load({params:{list_id: currList}}); // Filter the data store from the currList value
});

// *************************************************************************************
// Structure and load the data for ListsOptions
// AJAX -> data_*.ejs.php
// *************************************************************************************
var storeListsOption = new Ext.data.Store({
	autoSave	: false,

	// HttpProxy will only allow requests on the same domain.
	proxy : new Ext.data.HttpProxy({
		method		: 'POST',
		api: {
			read	: '../administration/lists/data_read.ejs.php',
			create	: '../administration/lists/data_create.ejs.php',
			update	: '../administration/lists/data_update.ejs.php',
			destroy : '../administration/lists/data_destroy.ejs.php'
		}
	}),

	// JSON Writer options
	writer: new Ext.data.JsonWriter({
		returnJson		: true,
		writeAllFields	: true,
		listful			: true,
		writeAllFields	: true
	}, ListRecord ),

	// JSON Reader options
	reader: new Ext.data.JsonReader({
		idProperty: 'id',
		totalProperty: 'results',
		root: 'row'
	}, ListRecord )
	
});

// *************************************************************************************
// Facility Form
// Add or Edit purpose
// *************************************************************************************
var frmLists = new Ext.FormPanel({
	id			: 'frmLists',
	autoWidth	: true,
	border		: false,
	bodyStyle	: 'padding: 5px',
	defaults	: { labelWidth: 50 },
	items: 
	[
		{ xtype: 'textfield', width: 200, id: 'list_name', name: 'list_name', fieldLabel: '<?php echo htmlspecialchars( xl('List Name'), ENT_NOQUOTES); ?>' }
    ],
	
	// Window Bottom Bar
	bbar:[{
		text		:'<?php echo htmlspecialchars( xl('Save'), ENT_NOQUOTES); ?>',
		ref			: '../save',
		iconCls		: 'save',
		handler: function() {
			winLists.hide();
		}
	},{
		text:'<?php echo htmlspecialchars( xl('Close'), ENT_NOQUOTES); ?>',
		iconCls: 'delete',
		handler: function(){ winLists.hide(); }
	}]
});

// *************************************************************************************
// Message Window Dialog
// *************************************************************************************
var winLists = new Ext.Window({
	id			: 'winList',
	width		: 400,
	autoHeight	: true,
	modal		: true,
	resizable	: false,
	autoScroll	: false,
	title		: '<?php echo htmlspecialchars( xl('Create List'), ENT_NOQUOTES); ?>',
	closeAction	: 'hide',
	renderTo	: document.body,
	items: [ frmLists ]
}); // END WINDOW

// *************************************************************************************
// RowEditor Class
// *************************************************************************************
var editor = new Ext.ux.grid.RowEditor({
	saveText: 'Update'
});

// *************************************************************************************
// Create the GridPanel
// *************************************************************************************
var listGrid = new Ext.grid.GridPanel({
	id			: 'listGrid',
	store		: storeListsOption,
	stripeRows	: true,
	border		: false,    
	frame	  	: false,
	viewConfig	: {forceFit: true},
	sm			: new Ext.grid.RowSelectionModel({singleSelect:true}),
	columns: [
		// Viewable cells
		{ 	
			width: 50, 
			header: 'ID', 
			sortable: true, 
			dataIndex: 'option_id',
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
		},
		{ 
			width: 150, 
			header: '<?php echo htmlspecialchars( xl('Title'), ENT_NOQUOTES); ?>', 
			sortable: true, 
			dataIndex: 'title',
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
		},
		{ 
			header: '<?php echo htmlspecialchars( xl('Order'), ENT_NOQUOTES); ?>', 
			sortable: true, 
			dataIndex: 'seq',
			editor: {
                xtype: 'textfield',
                allowBlank: false
            }
		},
		{ 
			header: '<?php echo htmlspecialchars( xl('Default'), ENT_NOQUOTES); ?>', 
			sortable: true, 
			dataIndex: 'is_default',
            editor: {
                xtype: 'textfield',
                allowBlank: false
            } 
		},
		{ 
			header: '<?php echo htmlspecialchars( xl('Notes'), ENT_NOQUOTES); ?>', 
			sortable: true, 
			dataIndex: 'notes',
            editor: {
                xtype: 'textfield',
                allowBlank: true
            } 
		}
	],
	// -----------------------------------------
	// Grid Top Menu
	// -----------------------------------------
	tbar: [{
		xtype	:'button',
		id		: 'addList',
		text	: '<?php xl("Create a list", 'e'); ?>',
		iconCls	: 'icoListOptions',
		handler: function(){
			Ext.getCmp('frmLists').getForm().reset(); // Clear the form
			winLists.show();
		}
	},'-',{
		xtype		  :'button',
		id			  : 'delList',
		ref			  : '../delList',
		text		  : '<?php xl("Delete list", 'e'); ?>',
		iconCls		: 'delete',
	},'-','<?php xl("Select list", 'e'); ?>: ',{
		name			: 'cmbList', 
		width			: 250,
		xtype			: 'combo',
		displayField	: 'title',
		id				: 'cmbList',
		mode			: 'local',
		triggerAction	: 'all', 
		hiddenName		: 'option_id',
		valueField		: 'option_id',
		ref				: '../cmbList',
		iconCls			: 'icoListOptions',
		editable		: false,
		store			: storeEditList,
		ctCls			: 'fieldMark',
		listeners: {
			select: function( cmb, rec, indx){
				// Reload the data store to reflect the new selected filter
				storeListsOption.reload({params:{list_id: rec.data.option_id }});
			}
		}
	}], // END GRID TOP MENU
	// -----------------------------------------
	// Grid Bottom Menu
	// -----------------------------------------
	bbar:[{
		text		:'<?php echo htmlspecialchars( xl('Add record'), ENT_NOQUOTES); ?>',
		ref			: '../add',
		iconCls		: 'add',
		handler: function() { }
	},{
		text:'<?php echo htmlspecialchars( xl('Delete record'), ENT_NOQUOTES); ?>',
		iconCls: 'delete',
		handler: function(){ }
	}], // END GRID BOTTOM BAR
	plugins: [editor, new Ext.ux.grid.Search({
		mode			: 'local',
		iconCls			: false,
		deferredRender	: false,
		dateFormat		: 'm/d/Y',
		minLength		: 4,
		align			: 'left',
		width			: 250,
		disableIndexes	: ['id'],
		position		: 'top'
	})]			
}); // END GRID


//******************************************************************************
// Render Panel
// This panel is mandatory for all layouts.
//******************************************************************************
var RenderPanel = new Ext.Panel({
  title: '<?php xl('List Options', 'e'); ?>',
  border  : false,
  stateful: true,
  monitorResize: true,
  autoWidth: true,
  id: 'RenderPanel',
  renderTo: Ext.getCmp('TopPanel').body,
  viewConfig:{forceFit:true},
  items: [ 
    listGrid
  ],
  listeners:{
	resize: function(){
		Ext.getCmp('listGrid').setHeight( Ext.getCmp('TopPanel').getHeight()-27 ); // -27 to show the bottom bar
	}
  }
});

//******************************************************************************
// Get the actual height of the TopPanel and apply it to this panel
// This is mandatory statement.
//******************************************************************************
Ext.getCmp('RenderPanel').setHeight( Ext.getCmp('TopPanel').getHeight() );

}); // End ExtJS

</script>
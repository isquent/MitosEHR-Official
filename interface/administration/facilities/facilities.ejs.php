<?php 
//******************************************************************************
// facilities.ejs.php
// Description: Facilities Screen
// v0.0.3
// 
// Author: Gino Rivera Falú
// Modified: n/a
// 
// MitosEHR (Eletronic Health Records) 2011
//******************************************************************************

session_name ( "MitosEHR" );
session_start();
session_cache_limiter('private');

include_once("../../../library/I18n/I18n.inc.php");

//******************************************************************************
// Reset session count 10 secs = 1 Flop
//******************************************************************************
$_SESSION['site']['flops'] = 0;

?>

<script type="text/javascript">

// *************************************************************************************
// Sencha trying to be like a language
// using requiered to load diferent components
// *************************************************************************************
Ext.require([
    'Ext.grid.*',
    'Ext.data.*',
    'Ext.util.*',
    'Ext.state.*'
]);

Ext.onReady(function() {
	
	Ext.QuickTips.init();
	
	var rowPos; // Stores the current Grid Row Position (int)
	var currRec; // Store the current record (Object)
	
	// *************************************************************************************
	// If a object called winUser exists destroy it, to create a new one.
	// *************************************************************************************
	if ( Ext.getCmp('winFacilities') ){ Ext.getCmp('winFacilities').destroy(); }
	
	// *************************************************************************************
	// Facility Record Structure
	// *************************************************************************************
	Ext.regModel('FacilitiesRecord', { fields: [
			{name: 'id',					type: 'int'},
			{name: 'name',					type: 'string'},
			{name: 'phone',					type: 'string'},
			{name: 'fax',					type: 'string'},
			{name: 'street',				type: 'string'},
			{name: 'city',					type: 'string'},
			{name: 'state',					type: 'string'},
			{name: 'postal_code',			type: 'string'},
			{name: 'country_code',			type: 'string'},
			{name: 'federal_ein',			type: 'string'},
			{name: 'service_location',		type: 'string'},
			{name: 'billing_location',		type: 'string'},
			{name: 'accepts_assignment',	type: 'string'},
			{name: 'pos_code',				type: 'string'},
			{name: 'x12_sender_id',			type: 'string'},
			{name: 'attn',					type: 'string'},
			{name: 'domain_identifier',		type: 'string'},
			{name: 'facility_npi',			type: 'string'},
			{name: 'tax_id_type',			type: 'string'}
		]
	});
	var FacilityStore = new Ext.data.Store({
		model: 'FacilitiesRecord',
    	noCache		: true,
    	autoSync	: false,
    	proxy		: {
    		type	: 'ajax',
			api		: {
				read	: 'interface/administration/facilities/data_read.ejs.php',
				create	: 'interface/administration/facilities/data_create.ejs.php',
				update	: 'interface/administration/facilities/data_update.ejs.php',
				destroy : 'interface/administration/facilities/data_destroy.ejs.php'
			},
        	reader: {
	            type			: 'json',
    	        idProperty		: 'idusers',
        	    totalProperty	: 'totals',
            	root			: 'row'
    		},
    		writer: {
    			type			: 'json',
    			writeAllFields	: true,
    			allowSingle		: false,
    			encode			: true,
    			root			: 'row'
    		}
    	},
    	autoLoad: true
	});

	// *************************************************************************************
	// POS Code Data Store
	// *************************************************************************************
	Ext.regModel('poscodeRecord', { fields: [
			{name: 'option_id',		type: 'string'},
			{name: 'title',			type: 'string'}
		]
	});
	var storePOSCode = new Ext.data.Store({
    	model		: 'poscodeRecord',
    	proxy		: {
	   		type	: 'ajax',
			api		: {
				read	: 'interface/administration/facilities/component_data.ejs.php?task=poscodes'
			},
    	   	reader: {
        	    type			: 'json',
   	        	idProperty		: 'id',
	       	    totalProperty	: 'totals',
    	       	root			: 'row'
   			}
   		},
    	autoLoad: true
	});

	// *************************************************************************************
	// User form
	// *************************************************************************************
    var facilityForm = Ext.create('Ext.form.Panel', {
    	frame: false,
    	border: false,
    	id: 'facilityForm',
        bodyStyle:'padding:2px',
        fieldDefaults: {
            msgTarget: 'side',
            labelWidth: 100
        },
        defaultType: 'textfield',
        defaults: {
            anchor: '100%'
        },
        items: [{
            fieldLabel: '<?php i18n("Name"); ?>',
            name: 'name',
        },{
            fieldLabel: '<?php i18n("Phone"); ?>',
            name: 'phone',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("Fax"); ?>',
            name: 'fax',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("Street"); ?>',
            name: 'street',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("City"); ?>',
            name: 'city',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("State"); ?>',
            name: 'state',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("Postal Code"); ?>',
            name: 'postal_code',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("Country Code"); ?>',
            name: 'country_code',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("Tax ID"); ?>',
            name: 'federal_ein',
            allowBlank: false,
        },{
        	xtype: 'checkboxfield',
            fieldLabel: '<?php i18n("Service Location"); ?>',
            name: 'service_location',
        },{
        	xtype: 'checkboxfield',
            fieldLabel: '<?php i18n("Billing Location"); ?>',
            name: 'billing_location',
        },{
        	xtype: 'checkboxfield',
            fieldLabel: '<?php i18n("Accepts assignment"); ?>',
            name: 'accepts_assignment',
        },{
			fieldLabel: '<?php i18n("POS Code"); ?>',
			xtype: 'combo', 
			id: 'cmbPOSCode', 
			displayField: 'title', 
			editable: false, 
			store: storePOSCode, 
			queryMode: 'local',
            name: 'pos_code',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("X12 Sender ID"); ?>',
            name: 'x12_sender_id',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("Attn"); ?>',
            name: 'attn',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("Domain identifier"); ?>',
            name: 'domain_identifier',
            allowBlank: false,
        },{
            fieldLabel: '<?php i18n("NPI Number"); ?>',
            name: 'facility_npi',
            allowBlank: false,
        },{
        	name: 'id',
        	hidden: true
        }],

        buttons: [{
            text: 'Save',
            handler: function(){
				//----------------------------------------------------------------
				// Check if it has to add or update
				// Update: 
				// 1. Get the record from store, 
				// 2. get the values from the form, 
				// 3. copy all the 
				// values from the form and push it into the store record.
				// Add: The re-formated record to the dataStore
				//----------------------------------------------------------------
				if (faciltyForm.getForm().findField('id').getValue()){ // Update
					var record = FacilityStore.getAt(rowPos);
					var fieldValues = faciltyForm.getForm().getValues();
					for ( k=0; k <= record.fields.getCount()-1; k++) {
						i = record.fields.get(k).name;
						record.set( i, fieldValues[i] );
					}
				} else { // Add
					//----------------------------------------------------------------
					// 1. Convert the form data into a JSON data Object
					// 2. Re-format the Object to be a valid record (UserRecord)
					// 3. Add the new record to the datastore
					//----------------------------------------------------------------
					var obj = eval( '(' + Ext.JSON.encode(faciltyForm.getForm().getValues()) + ')' );
					var rec = new usersRecord(obj);
					FacilityStore.add( rec );
				}
				FacilityStore.save();          // Save the record to the dataStore
				winFacility.hide();				// Finally hide the dialog window
				FacilityStore.load();			// Reload the dataSore from the database
			}
        },{
            text: 'Cancel',
            handler: function(){
            	winFacility.hide();
            }
        }]
    });
    
	// *************************************************************************************
	// Window User Form
	// *************************************************************************************
	var winFacility = Ext.create('widget.window', {
		id			: 'winFacility',
		closable	: true,
		closeAction	: 'hide',
		width		: 450,
		height		: 530,
		resizable	: false,
		modal		: true,
		bodyStyle	: 'background-color: #ffffff; padding: 5px;',
		items		: [ facilityForm ]
	});
	

	// *************************************************************************************
	// Facility Grid Panel
	// *************************************************************************************
	var FacilityGrid = Ext.create('Ext.grid.Panel', {
		store		: FacilityStore,
        columnLines	: true,
        frame		: false,
        frameHeader	: false,
        border		: false,
        layout		: 'fit',
        columns: [
			{
				text     : '<?php i18n("Name"); ?>',
				flex     : 1,
				sortable : true,
				dataIndex: 'name'
            },
            {
				text     : '<?php i18n("Phone"); ?>',
				width    : 100,
				sortable : true,
				dataIndex: 'phone'
            },
            {
				text     : '<?php i18n("Fax"); ?>',
				width    : 100,
				sortable : true,
				dataIndex: 'fax'
            },
            {
				text     : '<?php i18n("City"); ?>',
				width    : 100,
				sortable : true,
				dataIndex: 'city'
            }
		],
		viewConfig: { stripeRows: true },
		listeners: {
			itemclick: {
            	fn: function(DataView, record, item, rowIndex, e){ 
            		Ext.getCmp('facilityForm').getForm().reset(); // Clear the form
            		Ext.getCmp('cmdEdit').enable();
            		Ext.getCmp('cmdDelete').enable();
					var rec = FacilityStore.getAt(rowIndex);
					Ext.getCmp('facilityForm').getForm().loadRecord(rec);
            		currRec = rec;
            		rowPos = rowIndex;
            	}
			},
			itemdblclick: {
            	fn: function(DataView, record, item, rowIndex, e){ 
            		Ext.getCmp('facilityForm').getForm().reset(); // Clear the form
            		Ext.getCmp('cmdEdit').enable();
            		Ext.getCmp('cmdDelete').enable();
					var rec = FacilityStore.getAt(rowIndex);
					Ext.getCmp('facilityForm').getForm().loadRecord(rec);
            		currRec = rec;
            		rowPos = rowIndex;
            		winFacility.setTitle('<?php i18n("Edit Facility"); ?>');
            		winFacility.show();
            	}
			}
		}
    }); // END Facility Grid

	// *************************************************************************************
	// Top Render Panel
	// *************************************************************************************
	var topRenderPanel = Ext.create('Ext.Panel', {
		title		: '<?php i18n("Facilities"); ?>',
		renderTo	: Ext.getCmp('MainApp').body,
		layout		: 'fit',
		height		: Ext.getCmp('MainApp').getHeight(),
  		frame		: false,
  		border		: false,
		bodyPadding	: 0,
		id			: 'topRenderPanel',
		items: [ FacilityGrid ],
		dockedItems: [{
			xtype: 'toolbar',
			dock: 'top',
			items: [{
				text: '<?php i18n("Add Facility"); ?>',
				iconCls: 'icoAddRecord',
				handler: function(){
					Ext.getCmp('facilityForm').getForm().reset(); // Clear the form
					winFacility.show();
					winFacility.setTitle('<?php i18n("Add Facility"); ?>'); 
				}
			},'-',{
				text: '<?php i18n("Edit Facility"); ?>',
				iconCls: 'edit',
				id: 'cmdEdit',
				disabled: true,
				handler: function(){
      				winFacility.setTitle('<?php i18n("Edit Facility"); ?>');
					winFacility.show(); 
				}
			},'-',{
				text: '<?php i18n("Delete Facility"); ?>',
				iconCls: 'delete',
				disabled: true,
				id: 'cmdDelete',
				handler: function(){
					Ext.Msg.show({
						title: '<?php i18n('Please confirm...'); ?>', 
						icon: Ext.MessageBox.QUESTION,
						msg:'<?php i18n('Are you sure to delete this Facility?'); ?>',
						buttons: Ext.Msg.YESNO,
						fn:function(btn,msgGrid){
							if(btn=='yes'){
								storeUsers.remove( currRec );
								storeUsers.save();
								storeUsers.load();
			    		    }
						}
					});
				}
			}]
    	}]
	}); // END TOP PANEL

}); // End ExtJS

</script>
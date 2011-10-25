//******************************************************************************
// facilities.ejs.php
// Description: Facilities Screen
// v0.0.3
// 
// Author: GI Technologies, 2011
// Modified: n/a
// 
// MitosEHR (Eletronic Health Records) 2011
//******************************************************************************

Ext.define('Ext.mitos.panel.administration.facilities.Facilities',{
    extend      : 'Ext.mitos.RenderPanel',
    id          : 'panelFacilities',
    pageTitle   : 'Facilities',
    uses        : [ 'Ext.mitos.CRUDStore', 'Ext.mitos.GridPanel', 'Ext.mitos.SaveCancelWindow' ],
    initComponent: function(){
        /** @namespace Ext.QuickTips */
        Ext.QuickTips.init();

        var panel = this;
        var rowPos; // Stores the current Grid Row Position (int)
        var currRec; // Store the current record (Object)

        // *************************************************************************************
        // Facility Record Structure
        // *************************************************************************************
        panel.FacilityStore = Ext.create('Ext.mitos.CRUDStore',{
            fields: [
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
            ],
            model 		:'facilityModel',
            idProperty 	:'id',
            read		:'app/administration/facilities/data_read.ejs.php',
            create		:'app/administration/facilities/data_create.ejs.php',
            update		:'app/administration/facilities/data_update.ejs.php',
            destroy		:'app/administration/facilities/data_destroy.ejs.php'
        });

        // *************************************************************************************
        // POS Code Data Store
        // *************************************************************************************
        panel.storePOSCode = Ext.create('Ext.mitos.CRUDStore',{
            fields: [
                {name: 'option_id',		type: 'string'},
                {name: 'title',			type: 'string'}
            ],
                model 		:'posModel',
                idProperty 	:'id',
                read		:'app/administration/facilities/component_data.ejs.php',
                extraParams	: {"task": "poscodes"}
        });

        // *************************************************************************************
        // Federal EIN - TaxID Data Store
        // *************************************************************************************
        panel.storeTAXid = Ext.create('Ext.mitos.CRUDStore',{
            fields: [
                {name: 'option_id',		type: 'string'},
                {name: 'title',			type: 'string'}
            ],
                model 		:'taxidRecord',
                idProperty 	:'id',
                read		:'app/administration/facilities/component_data.ejs.php',
                extraParams	: {"task": "taxid"}
        });

        // *************************************************************************************
        // User form
        // *************************************************************************************
        panel.facilityForm = Ext.create('Ext.mitos.FormPanel', {
            fieldDefaults: { msgTarget: 'side', labelWidth: 100 },
            defaultType: 'textfield',
            defaults: { anchor: '100%' },
            items: [{
                fieldLabel: 'Name',
                name: 'name',
                allowBlank: false
            },{
                fieldLabel: 'Phone',
                name: 'phone',
                vtype: 'phoneNumber'
            },{
                fieldLabel: 'Fax',
                name: 'fax',
                vtype: 'phoneNumber'
            },{
                fieldLabel: 'Street',
                name: 'street'
            },{
                fieldLabel: 'City',
                name: 'city'
            },{
                fieldLabel: 'State',
                name: 'state'
            },{
                fieldLabel: 'Postal Code',
                name: 'postal_code',
                vtype: 'postalCode'
            },{
                fieldLabel: 'Country Code',
                name: 'country_code'
            },{
                xtype: 'fieldcontainer',
                fieldLabel: 'Tax ID',
                layout: 'hbox',
                items: [
                    panel.cmbTaxIdType = new Ext.create('Ext.form.ComboBox',{
                        displayField: 'title',
                        valueField: 'option_id',
                        editable: false,
                        store: panel.storeTAXid,
                        queryMode: 'local',
                        width: 50
                    })
                ,{
                    xtype: 'textfield',
                    name: 'federal_ein'
                }]
            },{
                xtype: 'checkboxfield',
                fieldLabel: 'Service Location',
                name: 'service_location'
            },{
                xtype: 'checkboxfield',
                fieldLabel: 'Billing Location',
                name: 'billing_location'
            },{
                xtype: 'checkboxfield',
                fieldLabel: 'Accepts assignment',
                name: 'accepts_assignment'
            },
                panel.cmbposCode = new Ext.create('Ext.form.ComboBox',{
                    fieldLabel: 'POS Code',
                    displayField: 'title',
                    valueField: 'option_id',
                    editable: false,
                    store: panel.storePOSCode,
                    queryMode: 'local'
                })
            ,{
                fieldLabel: 'Billing Attn',
                name: 'attn'
            },{
                fieldLabel: 'CLIA Number',
                name: 'domain_identifier'
            },{
                fieldLabel: 'Facility NPI',
                name: 'facility_npi'
            },{
                name: 'id',
                hidden: true
            }],
            listeners: {
                beforeshow: {
                    fn: function(){
                        panel.cmbTaxIdType.setValue( panel.storeTAXid.getAt(0).data.option_id );
                        panel.cmbposCode.setValue( panel.storePOSCode.getAt(0).data.option_id );
                    }
                }
            }
        });

        // *************************************************************************************
        // Window User Form
        // *************************************************************************************
        panel.winFacility = Ext.create('Ext.mitos.Window', {
            width		: 450,
            height		: 530,
            items		: [ panel.facilityForm ],
            buttons:[
                panel.cmdSave = Ext.create('Ext.Button', {
                    text		:'Save',
                    iconCls		: 'save',
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
                            if (panel.facilityForm.getForm().findField('id').getValue()){ // Update
                            var record = panel.FacilityStore.getAt(rowPos);
                            var fieldValues = panel.facilityForm.getForm().getValues();
                            var k, i;
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
                            var obj = eval( '(' + Ext.JSON.encode(panel.facilityForm.getForm().getValues()) + ')' );
                            panel.FacilityStore.add( obj );
                        }
                        panel.winFacility.hide();		// Finally hide the dialog window
                        panel.FacilityStore.sync();	// Save the record to the dataStore
                        panel.FacilityStore.load();	// Reload the dataSore from the database
                    }
                })
            ,'-',
                panel.cmdClose = Ext.create('Ext.Button', {
                    text:'Close',
                    iconCls: 'delete',
                    handler: function(){
                        panel.winFacility.hide();
                    }
                })
            ]
        });

        // *************************************************************************************
        // Facility Grid Panel
        // *************************************************************************************
        panel.FacilityGrid = Ext.create('Ext.mitos.GridPanel', {
            store		: panel.FacilityStore,
            columns: [
                {
                    text     : 'Name',
                    flex     : 1,
                    sortable : true,
                    dataIndex: 'name'
                },
                {
                    text     : 'Phone',
                    width    : 100,
                    sortable : true,
                    dataIndex: 'phone'
                },
                {
                    text     : 'Fax',
                    width    : 100,
                    sortable : true,
                    dataIndex: 'fax'
                },
                {
                    text     : 'City',
                    width    : 100,
                    sortable : true,
                    dataIndex: 'city'
                }
            ],
            // Slider bar or Pagin
            bbar: Ext.create('Ext.PagingToolbar', {
                pageSize: 30,
                store: panel.FacilityStore,
                displayInfo: true,
                plugins: Ext.create('Ext.ux.SlidingPager', {})
            }),
            listeners: {
                itemclick: {
                    fn: function(DataView, record, item, rowIndex, e){
                        panel.facilityForm.getForm().reset(); // Clear the form
                        panel.cmdEdit.enable();
                        panel.cmdDelete.enable();
                        var rec = panel.FacilityStore.getAt(rowIndex);
                        panel.facilityForm.getForm().loadRecord(rec);
                        currRec = rec;
                        rowPos = rowIndex;
                    }
                },
                itemdblclick: {
                    fn: function(DataView, record, item, rowIndex, e){
                        panel.facilityForm.getForm().reset(); // Clear the form
                        panel.cmdEdit.enable();
                        panel.cmdDelete.enable();
                        var rec = panel.FacilityStore.getAt(rowIndex);
                        panel.facilityForm.getForm().loadRecord(rec);
                        currRec = rec;
                        rowPos = rowIndex;
                        panel.winFacility.setTitle('Edit Facility');
                        panel.winFacility.show();
                    }
                }
            },
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'top',
                items: [
                    panel.cmdAddFacility = new Ext.create('Ext.Button', {
                        text: 'Add Facility',
                        iconCls: 'icoAddRecord',
                        handler: function(){
                            panel.facilityForm.getForm().reset(); // Clear the form
                            panel.winFacility.show();
                            panel.winFacility.setTitle('Add Facility');
                        }
                    })
                ,'-',
                    panel.cmdEdit = new Ext.create('Ext.Button', {
                        text: 'Edit Facility',
                        iconCls: 'edit',
                        disabled: true,
                        handler: function(){
                            panel.winFacility.setTitle('Edit Facility');
                            panel.winFacility.show();
                        }
                    })
                ,'-',
                    panel.cmdDelete = new Ext.create('Ext.Button', {
                        text: 'Delete Facility',
                        iconCls: 'delete',
                        disabled: true,
                        handler: function(){
                            Ext.Msg.show({
                                title: 'Please confirm...',
                                icon: Ext.MessageBox.QUESTION,
                                msg:'Are you sure to delete this Facility?',
                                buttons: Ext.Msg.YESNO,
                                fn:function(btn,msgGrid){
                                    if(btn=='yes'){
                                        panel.FacilityStore.remove( currRec );
                                        panel.FacilityStore.save();
                                        panel.FacilityStore.load();
                                    }
                                }
                            });
                        }
                    })
                ]
            }]
        }); // END Facility Grid
        panel.pageBody = [ panel.FacilityGrid ];
        panel.callParent(arguments);
    } // end of initComponent
}); //ens FacilitiesPanel class
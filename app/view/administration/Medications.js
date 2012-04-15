/**
 * services.ejs.php
 * Services
 * v0.0.1
 *
 * Author: Ernest Rodriguez
 *
 * MitosEHR (Electronic Health Records) 2011
 *
 *
 * @namespace Services.getServices
 * @namespace Services.addService
 * @namespace Services.updateService
 */
Ext.define('App.view.administration.Medications', {
	extend   : 'App.classes.RenderPanel',
	id       : 'panelMedications',
	pageTitle: 'Medications',

	initComponent: function() {
		var me = this;

		me.storeMedications = Ext.create('App.store.administration.Medications');

		me.medicationsGrid = Ext.create('App.classes.GridPanel', {
			region : 'center',
			store  : me.storeMedications,
			columns: [
				{
					width    : 70,
					header   : 'Number',
					dataIndex: 'PRODUCTNDC',
					sortable : true
				},
				{
					width    : 80,
					header   : 'Name',
					dataIndex: 'PROPRIETARYNAME',
					sortable : true
				},
				{
					width    : 200,
					header   : 'Active Comp',
					dataIndex: 'NONPROPRIETARYNAME',
					sortable : true
				},
				{
					width    : 175,
					header   : 'Dosage',
					dataIndex: 'DOSAGEFORMNAME',
					sortable : true
				},
				{
					width    : 45,
					header   : 'Number',
					dataIndex: 'ACTIVE_NUMERATOR_STRENGTH',
					sortable : true
				},
				{
					flex     : 1,
					header   : 'Unit',
					dataIndex: 'ACTIVE_INGRED_UNIT',
					sortable : true
				}
			],
			plugins: Ext.create('App.classes.grid.RowFormEditing', {
				autoCancel  : false,
				errorSummary: false,
				clicksToEdit: 1,
				formItems   : [
					{

						title  : 'general',
						xtype  : 'container',
						padding: 10,
						layout : 'vbox',
						items  : [
							{
								/**
								 * Line one
								 */
								xtype   : 'fieldcontainer',
								layout  : 'hbox',
								defaults: { margin: '0 10 5 0' },
								items   : [
									{
										xtype     : 'textfield',
										fieldLabel: 'Name',
										width     : 150,
										labelWidth: 50,
										name      : 'PROPRIETARYNAME',
										readOnly  : true

									},
									{
										xtype     : 'textfield',
										fieldLabel: 'Active Component',
										width     : 350,
										labelWidth: 125,
										name      : 'NONPROPRIETARYNAME'

									},

									{
										xtype     : 'textfield',
										fieldLabel: 'Dosage',
										width     : 200,
										labelWidth: 50,
										name      : 'DOSAGEFORMNAME'

									}
								]

							},
							{
								/**
								 * Line two
								 */
								xtype   : 'fieldcontainer',
								layout  : 'hbox',
								defaults: { margin: '0 10 5 0' },
								items   : [
									{
										xtype     : 'textfield',
										fieldLabel: 'Code',
										labelWidth: 50,
										width     : 150,
										name      : 'PRODUCTNDC'


									},
									{
										xtype     : 'numberfield',
										fieldLabel: 'Dosis',
										margin    : '0 0 5 0',
										value     : 0,
										minValue  : 0,
										width     : 275,
										labelWidth: 125,
										name      : 'ACTIVE_NUMERATOR_STRENGTH'

									},
									{
										xtype: 'textfield',
										name : 'ACTIVE_INGRED_UNIT',
										width: 75

									}
								]

							}

						]


					}
				]





			}),
			tbar   : Ext.create('Ext.PagingToolbar', {
				store      : me.storeMedications,
				displayInfo: true,
				emptyMsg   : "No Office Notes to display",
				plugins    : Ext.create('Ext.ux.SlidingPager', {


				})

			})
		})
		me.pageBody = [ me.medicationsGrid ];
		me.callParent(arguments);
	}, // end of initComponent




	/**
	 * This function is called from MitosAPP.js when
	 * this panel is selected in the navigation panel.
	 * place inside this function all the functions you want
	 * to call every this panel becomes active
	 */
	onActive: function() {
		this.storeMedications.load();

	}
}); //ens servicesPage class
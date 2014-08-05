var ln =  {
	en: {
		title: 'B1 panel',
		
		import_products: {
			button: 'Import products',
			success: {
				label: 'Import products',
				message: '{count_created} products where created. {count_existing} products already exist.'
			}
		},
		
		import_quantities: {
			button: 'Import quantities',
			success: {
				label: 'Import quantities',
				message: '{count_updated} / {count_existing} products where updated from {count_total} products from api.'
			}
		},
		
		error: {
			error: 'Error',
			json: 'Invalid JSON data returned.',
			connection: 'Connection error.'
			
		}
	},
	lt: {
		title: 'B1 panelė',
		import_products: {
			button: 'Importuoti produktus',
			success: {
				label: 'Importuoti produktus',
				message: 'Sukurta {count_created} produktų. {count_existing} jau egzistavo.'
			}
		},
		
		import_quantities: {
			button: 'Importuoti likučius',
			success: {
				label: 'Importuoti likučius',
				message: '{count_updated} / {count_existing} produktų buvo atnaujinta iš {count_total} produktų iš API.'
			}
		},
		error: {
			error: 'Klaida',
			json: 'Neteisingi JSON duomenys.',
			connection: 'Ryšio klaida.'
		}
	},
	ru: {
		title: 'B1 Панель администрирования',
		import_products: {
			button: 'Импортировать продукты',
			success: {
				label: 'Импортировать продукты',
				message: '{count_created} products where created. {count_existing} products already exist.'
			}
		},
		
		import_quantities: {
			button: 'Импортировать остатки',
			success: {
				label: 'Импортировать остатки',
				message: '{count_updated} / {count_existing} products where updated from {count_total} products from api.'
			}
		},
		error: {
			error: 'Ошибка',
			json: 'Неверные данные JSON.',
			connection: 'Ошибка соединения.'
		}
	}
}

PC.utils.localize('mod.b1', ln);

Plugin_pc_shop_b1_panel = Ext.extend(Ext.Panel, {
	
	api_url: 'api/plugin/b1/import/',
	
	layout: 'fit',
	
	constructor: function(config) {
		if (!config) {
			config = {};
		}
		this.ln = this.get_ln();

		if (config.api_url) {
			this.api_url = config.api_url;
		}

		if (config.ln) {
			if (this.ln.error && config.ln.error) {
				config.ln.error = Ext.apply(this.ln.error, config.ln.error);
			}
			Ext.apply(this.ln, config.ln);
			delete config.ln;
		}

		config = Ext.apply({
			tbar: this.get_tbar_buttons()
			//items: this.get_items()
        }, config);

        Plugin_pc_shop_b1_panel.superclass.constructor.call(this, config);
		
		this.set_titles();
    },
	
	set_titles: function() {
		if (!this.title) {
			this.title = this.ln.title;
		}
	},
	
	get_default_ln: function() {
		return {};
	},
        
	get_ln: function() {
        var ln = {};
		ln = Ext.apply(ln, PC.i18n.mod.b1);
		ln = Ext.apply(ln, this.get_default_ln());
		return ln;
	},
	
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_import_products(),
			this.get_button_for_import_quantities()
		];
		return buttons;
	},
	
	get_button_for_import_products: function() {
		return {	
			ref: '../action_import_products',
			text: this.ln.import_products.button,
			icon: 'images/pencil.png',
			handler: Ext.createDelegate(this.button_handler_for_import_products, this)
		}
	},
	
	get_button_for_import_quantities: function() {
		return {	
			ref: '../action_import_quantites',
			text: this.ln.import_quantities.button,
			icon: 'images/pencil.png',
			handler: Ext.createDelegate(this.button_handler_for_import_quantities, this)
		}
	},
	
	
	button_handler_for_import_products: function() {
		Ext.Ajax.request({
			url: this.api_url + 'katalogas',
			method: 'POST',
			callback: Ext.createDelegate(this.ajax_response_for_import_products, this)
		});
		
	},
			
	ajax_response_for_import_products: function(opts, success, response) {
		var data = Ext.decode(response.responseText);
		if (data.error) {
			Ext.Msg.alert(this.ln.error.error, data.error_message);
		}
		else if (data.success) {
			var t = new Ext.Template(this.ln.import_products.success.message);
			var message = t.applyTemplate(data);
			Ext.Msg.alert(this.ln.import_products.success.label, message);
		}
	},
	
	button_handler_for_import_quantities: function() {
		Ext.Ajax.request({
			url: this.api_url + 'likuciai',
			method: 'POST',
			callback: Ext.createDelegate(this.ajax_response_for_import_quantities, this)
		});
		
	},
			
	ajax_response_for_import_quantities: function(opts, success, response) {
		var data = Ext.decode(response.responseText);
		if (data.error) {
			Ext.Msg.alert(this.ln.error.error, data.error_message);
		}
		else if (data.success) {
			var t = new Ext.Template(this.ln.import_quantities.success.message);
			var message = t.applyTemplate(data);
			Ext.Msg.alert(this.ln.import_quantities.success.label, message);
		}
	}
});



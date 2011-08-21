og.addNewCompany = function(genid){
	var show = document.getElementById(genid + 'new_company').style.display == 'none';
	document.getElementById(genid + 'new_company').style.display = show ? 'block':'none';
	document.getElementById(genid + 'existing_company').style.display = show? 'none': 'block';
	document.getElementById(genid + 'hfIsNewCompany').value = show;
	document.getElementById(genid + 'duplicateCompanyName').style.display = 'none';
	document.getElementById(genid + 'profileFormNewCompanyName').value = '';
	if (show) document.getElementById(genid + 'profileFormNewCompanyName').focus();
	Ext.get(genid + 'submit1').dom.disabled = false;
	Ext.get(genid + 'submit2').dom.disabled = false;
};

og.checkNewCompanyName = function(genid) {
	var fff = document.getElementById(genid + 'profileFormNewCompanyName');
	var name = fff.value.toUpperCase();
	document.getElementById(genid + 'duplicateCompanyName').style.display = 'none';
	document.getElementById(genid + 'duplicateCompanyName').innerHTML = '';
	
	var select = document.getElementById(genid + 'profileFormCompany');
	for (var i = 1; i < select.options.length; i++){
		if (select.options[i].text.toUpperCase() == name){
			document.getElementById(genid + 'duplicateCompanyName').innerHTML = lang('duplicate company name', select.options[i].text, genid, i);
			document.getElementById(genid + 'companyInfo').style.display="none";
			document.getElementById(genid + 'duplicateCompanyName').style.display = 'block';
			Ext.get(genid + 'submit1').dom.disabled = true;
			Ext.get(genid + 'submit2').dom.disabled = true;
			document.getElementById(genid + 'duplicateCompanyName').focus();
			return;
		}
	}		
	Ext.get(genid + 'submit1').dom.disabled = false;
	Ext.get(genid + 'submit2').dom.disabled = false;
	document.getElementById(genid + 'companyInfo').style.display="block";
		
};

og.selectCompany = function(genid, index) {
	var select = document.getElementById(genid + 'profileFormCompany');
	select.selectedIndex = index;
	og.addNewCompany(genid);
	og.companySelectedIndexChanged(genid);
};

og.companySelectedIndexChanged = function(genid){
	/*var select = document.getElementById(genid + 'profileFormCompany');
	Ext.get(genid + 'submit1').dom.disabled = true;
	Ext.get(genid + 'submit2').dom.disabled = true;
	
    og.openLink(og.getUrl('company','get_company_data', {id: select.options[select.selectedIndex].value}), {
    	caller:this,
    	callback: function(success, data) {
    		if (success) {
				Ext.get(genid + 'submit1').dom.disabled = false;
				Ext.get(genid + 'submit2').dom.disabled = false;
				
    			if (data.id > 0){
	    			document.getElementById(genid + 'profileFormWAddress').value = data.address;
	    			document.getElementById(genid + 'profileFormWCity').value = data.city;
	    			document.getElementById(genid + 'profileFormWState').value = data.state;
					var list = document.getElementById(genid + 'profileFormWCountry');
					for (var i = 0; i < list.options.length; i++)
						if (list.options[i].value == data.country){
							list.selectedIndex = i;
							break;
						}
	    			document.getElementById(genid + 'profileFormWZipcode').value = data.zipcode;
	    			document.getElementById(genid + 'profileFormWWebPage').value = data.webpage;
	    			document.getElementById(genid + 'profileFormWPhoneNumber').value = data.phoneNumber;
	    			document.getElementById(genid + 'profileFormWFaxNumber').value = data.faxNumber;
    			}
    		}
    	}
    });*/
}


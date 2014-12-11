/* Transparent Redirect JavaScript to append the form with session token */
var Velocity = {
    mod10: function (e) {
        var digit, num, reverse, count, index;
        reverse = !0, count = 0, num = (e + "").split("").reverse();
        for (index = 0; index < num.length; index++) {
            digit = num[index], digit = parseInt(digit, 10);
            if (reverse = !reverse) digit *= 2;
            digit > 9 && (digit -= 9), count += digit
        }
        return count % 10 === 0
    },

    tokenizeForm: function(identitytoken, card, address, applicationprofileid, merchantprofileid, workflowid, callbackfunction) {
	
		var identitytoken = Velocity.base64_encode(identitytoken+":");
        var sessiontokenobj = Velocity.getSessionToken(identitytoken, callbackfunction);

        if (!sessiontokenobj)
            // Validate browser compatibility
            Velocity.complete({'code': 1, 'text': 'Browser is not supported!' }, callbackfunction);
        else {
            // XMLHttpRequest Error Handler
            sessiontokenobj.onerror = function() {		
                Velocity.complete({'code': 7, 'text': 'XMLHttpRequest Error occured. Request Failed' }, callbackfunction);
            };

            // Session Token Request response Handler
            sessiontokenobj.onload = function() {
			
                // Validate HTTP Status code
                if (sessiontokenobj.status == 400)
                    Velocity.complete({'code': sessiontokenobj.status, 'text': 'An invalid security token was provided(Identity Token)' }, callbackfunction)
				else if (sessiontokenobj.status == 401)
				    Velocity.complete({'code': sessiontokenobj.status, 'text': 'Unauthorized request may be Identity Token is not correct' }, callbackfunction)
				else if (typeof sessiontokenobj.status != "undefined" && sessiontokenobj.status != 200)
				    Velocity.complete({'code': sessiontokenobj.status, 'text': 'Some unexpected error for sessiontoken request' }, callbackfunction)
                else {
                    // process received session token
                    var sessiontoken = JSON.parse(sessiontokenobj.response); 
                    sessiontoken = Velocity.base64_encode(sessiontoken+":");

                    // Service information request
                    var serviceInfo = Velocity.getServiceInfo(sessiontoken, callbackfunction); // actual call.

                    // Service Info Request Error handler
                    serviceInfo.onerror = function() {		
                        Velocity.complete({'code': 10, 'text': 'Ajax Error in serviceinfo transaction, Request Failed' }, callbackfunction);
                    };

                    // Service Info Request response handler
                    serviceInfo.onload = function() {

						if (serviceInfo.status == 400)
							Velocity.complete({'code': serviceInfo.status, 'text': 'Security token is not valid(Session Token)' }, callbackfunction)
                        else if (typeof serviceInfo.status != "undefined" && serviceInfo.status !== 200)
                            Velocity.complete({'code': serviceInfo.status, 'text': 'Unexpected Error occured for serviceinfo request' }, callbackfunction)
                        else {
                                            
                            // Send Verify Request
                            var verifyresponse = Velocity.getverificationResponse(sessiontoken, workflowid, callbackfunction);
                            
                            // Error Handler for Verify Request
                            verifyresponse.onerror = function() {		
                                Velocity.complete({'code': 14, 'text': 'Ajax Error in verify transaction, Request Failed' }, callbackfunction);
                            };
                            
                            // Response Handler for Verify Request
                            verifyresponse.onload = function() {
                                // Validate gateway response as status and Error code
								xmldoc = verifyresponse.responseXML;
								errobj = xmldoc.getElementsByTagName("ErrorId");
								errmsgobj = xmldoc.getElementsByTagName("Reason");
								errRmsgobj = xmldoc.getElementsByTagName("ValidationErrors");

								if(typeof errobj[0] != "undefined" && typeof errmsgobj[0] != "undefined") {
									ErrorId = errobj[0].childNodes[0].nodeValue; // get Error code from response
									reson = errmsgobj[0].childNodes[0].nodeValue; // get Error response message
								}

								if(typeof errobj[0] != "undefined" && typeof errRmsgobj[0] != "undefined") {
									ErrorId = errobj[0].childNodes[0].nodeValue; // get error code from response
									RuleMessage = errRmsgobj[0].childNodes[0].childNodes[2].childNodes[0].nodeValue; // get rule error response message
								}

								if(typeof ErrorId != "undefined" && ErrorId == 326)
                                    Velocity.complete({'code': ErrorId, 'text': reson}, callbackfunction)
								else if(typeof ErrorId != "undefined" && ErrorId == 0)
                                    Velocity.complete({'code': ErrorId+30, 'text': RuleMessage}, callbackfunction)
                                else if(typeof verifyresponse.status != "undefined" && verifyresponse.status !== 200)
                                    Velocity.complete({'code': verifyresponse.status, 'text': 'Validation Errors Occurred' }, callbackfunction)
                                else {
								    // get xml response after verification.
                                    objsc = xmldoc.getElementsByTagName("StatusCode");
									objs = xmldoc.getElementsByTagName("Status");
									objpadt = xmldoc.getElementsByTagName("PaymentAccountDataToken");
									objCVResult = xmldoc.getElementsByTagName("CVResult");
									objAVSResult = xmldoc.getElementsByTagName("AVSResult");
								
									// get status code from response
									if(typeof objsc[0] != 'undefined') {
										StatusCode = objsc[0].childNodes[0].nodeValue;
									}
									                     
									// get status from response
									if(typeof objs[0] != 'undefined' && objs[0].childNodes[0].nodeValue == 'Successful'){
									  Status = objs[0].childNodes[0].nodeValue;
									}else{
									  Velocity.complete({'code': 21, 'text': 'Invalid account detail.' }, callbackfunction)
									}
									
									
									// get PaymentAccountDataToken from response
									if(typeof objpadt[0] != 'undefined' && objpadt[0].childNodes[0].nodeValue != ''){
									   paymentAccountDataToken = objpadt[0].childNodes[0].nodeValue;
									}else{
									   Velocity.complete({'code': 22, 'text': 'payment Account Data Token is not available in response' }, callbackfunction)
									}
									
									// get CVResult from response
									var CVResult;
									if(typeof objCVResult[0] != 'undefined' && objCVResult[0].childNodes[0].nodeValue != ''){
									   CVResult = objCVResult[0].childNodes[0].nodeValue;
									}else{
									   Velocity.complete({'code': 23, 'text': 'CVResult is not available in response' }, callbackfunction)
									}
									
									// get AVSResult from response
									var AVSResult, valuearr = [];
									if (typeof objAVSResult[0].childNodes[0].nodeValue != 'undefined') {
									   AVSResultlength = objAVSResult[0].childNodes.length;
									   for ( var i = 0; i < AVSResultlength; i++ ) {
											var key = objAVSResult[0].childNodes[i].nodeName;
											var value = xmldoc.getElementsByTagName(key)[0].childNodes[0].nodeValue;
											valuearr.push(value);	
									   }
									   AVSResult = {"ActualResult" : valuearr[0], "AddressResult" : valuearr[1], "CountryResult" : valuearr[2], "StateResult" : valuearr[3], "PostalCodeResult" : valuearr[4], "PhoneResult" : valuearr[5], "CardholderNameResult" : valuearr[6], "CityResult" : valuearr[7]};
									} else {
									   Velocity.complete({'code': 24, 'text': 'AVSResult is not available in response' }, callbackfunction)
									}
									
									cardSecurityData = {"AVSData": address, "CVResult": CVResult, "AVSResult": AVSResult};
									if(StatusCode == '000'){
									
										var result = {"CardSecurityData": cardSecurityData, "PaymentAccountDataToken": paymentAccountDataToken};
										result = JSON.stringify(result);
										result = Velocity.base64_encode(result);
										Velocity.complete({'code': 0, 'text': result}, callbackfunction);

									}else{
									
									    Velocity.complete({'code': StatusCode, 'text': Status}, callbackfunction);
									
									}
                                }
                            };
                            
                            var XML = Velocity.xml_creator(card, address, applicationprofileid, merchantprofileid);		

                            var xmldoc = (new DOMParser()).parseFromString(XML, "application/xml");
                            
                            //send request for verification.
                            verifyresponse.send(XML);	
                        }
                    };
                    // Send request service request
                    serviceInfo.send();
                }
            };
            // Send request session token.
            sessiontokenobj.send();
        }
    },
    
	// Validate Card number with Length
	valcard_num: function(cc_num) {
        return Velocity.mod10(cc_num) && cc_num.length != 0;
    },
	
	// Validate Card cvv
    valcvv_num: function(cvv) {
        return (cvv.length == 3);
    },
	
	// Validate Card Expiry date
	valcc_exp: function(cc_exp) {
        return cc_exp.length == 4;
    },
	
    getSessionToken: function(identitytoken, callbackfunction) {
	
        var sessiontokenobj = null, url = "https://api.cert.nabcommerce.com/REST/2.0.18/SvcInfo/token";
		
        // IE6 reports XmlHttpRequest as undefined.
        if (typeof XMLHttpRequest != "undefined") {
            sessiontokenobj = new XMLHttpRequest();
            
            // Prefer XMLHttpRequest (Chrome/Firefox/Opera/Safari/IE10).
            if ("withCredentials" in sessiontokenobj) {
               sessiontokenobj.open("GET", url, true);
			   sessiontokenobj.setRequestHeader("Authorization", "Basic "+identitytoken);
               sessiontokenobj.setRequestHeader("Content-type", "application/json");
			   sessiontokenobj.setRequestHeader("Accept", "");
            }
            // Fallback; attempt XDomainRequest (for IE8,9).
            else if (typeof XDomainRequest != "undefined") {
                sessiontokenobj = new XDomainRequest();
                sessiontokenobj.onprogress = function() { };
                sessiontokenobj.ontimeout = function() { };
                // Handle the exception of XDomainRequest.
                try {
                    sessiontokenobj.open("GET", url);
                }
                catch(e) {
                    Velocity.complete({'code': 8, 'text': 'Ajax request error occured for session token request in IE8,9' }, callbackfunction);
                    throw e; // Rethrow to terminate execution
                }
            }
            // sessiontokenobj Unsupported (IE7).
            else
                sessiontokenobj = null;
        }
        return sessiontokenobj;
    },
	
	getServiceInfo: function(sessiontoken, callbackfunction) {
		var sid = null, url = "https://api.cert.nabcommerce.com/REST/2.0.18/SvcInfo/serviceInformation";

        // IE6 reports XmlHttpRequest as undefined.
        if(typeof XMLHttpRequest != "undefined") {
            sid = new XMLHttpRequest();
            // Prefer XMLHttpRequest (Chrome/Firefox/Opera/Safari/IE10).
            if ("withCredentials" in sid) {
                sid.open("GET", url, true);
				sid.setRequestHeader("Authorization", "Basic "+sessiontoken);
                sid.setRequestHeader("Content-type", "application/json");
                sid.setRequestHeader("Accept", "");
            }
            // Fallback; attempt XDomainRequest (for IE8,9).
            else if (typeof XDomainRequest != "undefined") {
                sid = new XDomainRequest();
                sid.onprogress = function() { };
                sid.ontimeout = function() { };
                
                // Handle the exception of XDomainRequest.
                try {
                    sid.open("GET", url);
                }
                catch(e) {
                    Velocity.complete({'code': 9, 'text': 'Ajax request error occured for service information request in IE8,9' }, callbackfunction)
                    throw e; // Rethrow to terminate execution
                }
            }
            // sessiontokenobj Unsupported (IE7).
            else
                sid = null;
        }
        return sid;
    },
	
	getverificationResponse : function(sessiontoken, workflowid, callbackfunction) {
        var wid = null, url = "https://api.cert.nabcommerce.com/REST/2.0.18/Txn/"+workflowid+"/verify";

        // IE6 reports XmlHttpRequest as undefined.
        if(typeof XMLHttpRequest != "undefined") {
            wid = new XMLHttpRequest();
            // Prefer XMLHttpRequest (Chrome/Firefox/Opera/Safari/IE10).
            if ("withCredentials" in wid) {
                wid.open("POST", url, true);
				wid.setRequestHeader("Authorization", "Basic "+sessiontoken);
                wid.setRequestHeader("Content-type", "application/xml");
                wid.setRequestHeader("Accept", "");
            }
            // Fallback; attempt XDomainRequest (for IE8,9).
            else if (typeof XDomainRequest != "undefined") {
                wid = new XDomainRequest();
                wid.onprogress = function() { };
                wid.ontimeout = function() { };
                
                // Handle the exception of XDomainRequest.
                try {
                    wid.open("POST", url);
                }
                catch(e) {
                    Velocity.complete({'code': 13, 'text': 'Ajax request error occured for verify AVS & card data request in IE8,9' }, callbackfunction)
                    throw e; // Rethrow to terminate execution
                }
            }
            // sessiontokenobj Unsupported (IE7).
            else
                wid = null;
        }
        return wid;
    },
		
	base64_encode: function(data) {

	  var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	  var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
		ac = 0,
		enc = '',
		tmp_arr = [];

	  if (!data) {
		return data;
	  }

	  data = unescape(encodeURIComponent(data));

	  do {
		// pack three octets into four hexets
		o1 = data.charCodeAt(i++);
		o2 = data.charCodeAt(i++);
		o3 = data.charCodeAt(i++);

		bits = o1 << 16 | o2 << 8 | o3;

		h1 = bits >> 18 & 0x3f;
		h2 = bits >> 12 & 0x3f;
		h3 = bits >> 6 & 0x3f;
		h4 = bits & 0x3f;

		// use hexets to index into b64, and append result to encoded string
		tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
	  } while (i < data.length);

	  enc = tmp_arr.join('');

	  var r = data.length % 3;

	  return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
	},
	
	xml_creator: function(card, address, applicationprofileid, merchantprofileid) {
	
        var XML = '<AuthorizeTransaction xmlns:i="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.ipcommerce.com/CWS/v2.0/Transactions/Rest" i:type="AuthorizeTransaction">';
        XML	+= '<ApplicationProfileId>' + applicationprofileid + '</ApplicationProfileId>';
        XML	+= '<MerchantProfileId>' + merchantprofileid + '</MerchantProfileId>';
        XML	+= '<Transaction xmlns:ns1="http://schemas.ipcommerce.com/CWS/v2.0/Transactions/Bankcard" i:type="ns1:BankcardTransaction">';
        XML	+= '<ns1:TenderData>';
        XML	+= '<ns1:CardData>';
        XML	+= '<ns1:CardType>' + card.cardtype + '</ns1:CardType>';
        XML	+= '<ns1:CardholderName>' + card.CardholderName + '</ns1:CardholderName>';
        XML	+= '<ns1:PAN>' + parseInt(card.number) + '</ns1:PAN>';
        XML	+= '<ns1:Expire>' + card.expMonth+card.expYear + '</ns1:Expire>';
        XML	+= '<ns1:Track1Data i:nil="true"/>';
        XML	+= '</ns1:CardData>';
        XML	+= '<ns1:CardSecurityData>';
        XML	+= '<ns1:AVSData>';
        XML	+= '<ns1:CardholderName i:nil="true"/>';
        XML	+= '<ns1:Street>' + address.Street + '</ns1:Street>';
        XML	+= '<ns1:City>'+ address.City +'</ns1:City>';
        XML	+= '<ns1:StateProvince>'+address.StateProvince+'</ns1:StateProvince>';
        XML	+= '<ns1:PostalCode>'+ parseInt(address.PostalCode) +'</ns1:PostalCode>';
        XML	+= '<ns1:Phone>'+ address.Phone +'</ns1:Phone>';
        XML	+= '<ns1:Email i:nil="true"/>';
        XML	+= '</ns1:AVSData>';
        XML	+= '<ns1:CVDataProvided>Provided</ns1:CVDataProvided>';
        XML	+= '<ns1:CVData>' + parseInt(card.cvc) + '</ns1:CVData>';
        XML	+= '<ns1:KeySerialNumber i:nil="true"/>';
        XML	+= '<ns1:PIN i:nil="true"/>';
        XML	+= '<ns1:IdentificationInformation i:nil="true"/>';
        XML	+= '</ns1:CardSecurityData>';
        XML	+= '<ns1:EcommerceSecurityData i:nil="true"/>';
        XML	+= '</ns1:TenderData>';
        XML	+= '<ns1:TransactionData>';
        XML	+= '<ns8:Amount xmlns:ns8="http://schemas.ipcommerce.com/CWS/v2.0/Transactions">0.00</ns8:Amount>';
        XML	+= '<ns9:CurrencyCode xmlns:ns9="http://schemas.ipcommerce.com/CWS/v2.0/Transactions">USD</ns9:CurrencyCode>';
        XML	+= '<ns10:TransactionDateTime xmlns:ns10="http://schemas.ipcommerce.com/CWS/v2.0/Transactions">2013-04-03T13:50:16</ns10:TransactionDateTime>';
        XML	+= '<ns1:CustomerPresent>Ecommerce</ns1:CustomerPresent>';
        XML	+= '<ns1:EmployeeId>11</ns1:EmployeeId>';
        XML	+= '<ns1:EntryMode>Keyed</ns1:EntryMode>';
        XML	+= '<ns1:IndustryType>Ecommerce</ns1:IndustryType>';
        XML	+= '</ns1:TransactionData>';
        XML	+= '</Transaction>';
        XML	+= '</AuthorizeTransaction>';

        return XML;
	},
	
 	complete: function(response, callbackfunction) {
		return typeof callbackfunction == "function" ? callbackfunction(response) : void 0;
	}
}
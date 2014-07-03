<?php
namespace Oranges\forms;

class StdData
{
	public static function getStates()
	{
		$vals = array();
		$vals['Alabama'] = "AL";
		$vals['Alaska'] = "AK";
		$vals['Arizona'] = "AS";
		$vals['Arkansas'] = "AR";
		$vals['California'] = "CA";
		$vals['Colorado'] = "CO";
		$vals['Connecticut'] = "CT";
		$vals['Delaware'] = "DE";
		$vals['District of Columbia'] = "DC";
		$vals['Florida'] = "FL";
		$vals['Georgia'] = "GA";
		$vals['Hawaii'] = "HI";
		$vals['Idaho'] = "ID";
		$vals['Illinois'] = "IL";
		$vals['Indiana'] = "IN";
		$vals['Iowa'] = "IA";
		$vals['Kansas'] = "KS";
		$vals['Kentucky'] = "KY";
		$vals['Louisiana'] = "LA";
		$vals['Maine'] = "ME";
		$vals['Maryland'] = "MD";
		$vals['Massachusetts'] = "MA";
		$vals['Michigan'] = "MI";
		$vals['Minnesota'] = "MN";
		$vals['Mississippi'] = "MS";
		$vals['Missouri'] = "MO";
		$vals['Montana'] = "MT";
		$vals['Nebraska'] = "NE";
		$vals['Nevada'] = "NV";
		$vals['New Hampshire'] = "NH";
		$vals['New Jersey'] = "NJ";
		$vals['New Mexico'] = "NM";
		$vals['New York'] = "NY";
		$vals['North Carolina'] = "NC";
		$vals['North Dakota'] = "ND";
		$vals['Ohio'] = "OH";
		$vals['Oklahoma'] = "OK";
		$vals['Oregon'] = "OR";
		$vals['Pennsylvania'] = "PA";
		$vals['Rhode Island'] = "RI";
		$vals['South Carolina'] = "SC";
		$vals['South Dakota'] = "SD";
		$vals['Tennessee'] = "TN";
		$vals['Texas'] = "TX";
		$vals['Utah'] = "UT";
		$vals['Vermont'] = "VT";
		$vals['Virginia'] = "VI";
		$vals['Washington'] = "WA";
		$vals['West Virginia'] = "WV";
		$vals['Wisconsin'] = "WI";
		$vals['Wyoming'] = "WY";
		return $vals;
	}

	public static function getSecurityQuestion()
	{
		$vals = array();
		$vals['What is the name of your High School'] = 1;
		$vals['What is your Mothers maiden name?'] = 2;
		$vals['What was the name of your first pet?'] = 3;
		return $vals;
	}

	public static function getCountry()
	{

    	$cc = array();
		$cc['AF'] = "Afghanistan";
		$cc['AL'] = "Albania";
		$cc['DZ'] = "Algeria";
		$cc['AS'] = "American samoa";
		$cc['AD'] = "Andorra";
		$cc['AO'] = "Angola";
		$cc['AI'] = "Anguilla";
		$cc['AQ'] = "Antarctica";
		$cc['AG'] = "Antigua and Barbuda";
		$cc['AR'] = "Argentina";
		$cc['AM'] = "Armenia";
		$cc['AW'] = "Aruba";
		$cc['AU'] = "Australia";
		$cc['AT'] = "Austria";
		$cc['AZ'] = "Azerbaijan";
		$cc['BS'] = "Bahamas";
		$cc['BH'] = "Bahrain";
		$cc['BD'] = "Bangladesh";
		$cc['BB'] = "Barbados";
		$cc['BY'] = "Belarus";
		$cc['BE'] = "Belgium";
		$cc['BZ'] = "Belize";
		$cc['BJ'] = "Benin";
		$cc['BM'] = "Bermuda";
		$cc['BT'] = "Bhutan";
		$cc['BO'] = "Bolivia";
		$cc['BA'] = "Bosnia";
		$cc['BW'] = "Botswana";
		$cc['BV'] = "Bouvet Island";
		$cc['BR'] = "Brazil";
		$cc['BN'] = "Brunei Darussalam";
		$cc['BG'] = "Bulgaria";
		$cc['BF'] = "Burkina Faso";
		$cc['BI'] = "Burundi";
		$cc['KH'] = "Cambodia";
		$cc['CM'] = "Cameroon";
		$cc['CA'] = "Canada";
		$cc['CV'] = "Cape Verde";
		$cc['KY'] = "Cayman Islands";
		$cc['TD'] = "Chad";
		$cc['CL'] = "Chile";
		$cc['CN'] = "China";
		$cc['CX'] = "Christmas island";
		$cc['CO'] = "Colombia";
		$cc['KM'] = "Comoros";
		$cc['CG'] = "Congo";
		$cc['CK'] = "Cook Islands";
		$cc['CR'] = "Costa Rica";
		$cc['CI'] = "Cote D'ivoire";
		$cc['HR'] = "Croatia (Hrvatska)";
		$cc['CU'] = "Cuba";
		$cc['CY'] = "Cyprus";
		$cc['CZ'] = "Czech Republic";
		$cc['DK'] = "Denmark";
		$cc['DJ'] = "Djibouti";
		$cc['DM'] = "Dominica";
		$cc['DO'] = "Dominican Republic";
		$cc['TP'] = "East timor";
		$cc['EC'] = "Ecuador";
		$cc['EG'] = "Egypt";
		$cc['SV'] = "El Salvador";
		$cc['GQ'] = "Equatorial Guinea";
		$cc['ER'] = "Eritrea";
		$cc['EE'] = "Estonia";
		$cc['ET'] = "Ethiopia";
		$cc['FO'] = "Faroe Islands";
		$cc['FJ'] = "Fiji";
		$cc['FI'] = "Finland";
		$cc['FR'] = "France";
		$cc['FX'] = "France, Metropolitan";
		$cc['GF'] = "French Guiana";
		$cc['PF'] = "French Polynesia";
		$cc['GA'] = "Gabon";
		$cc['GM'] = "Gambia";
		$cc['GE'] = "Georgia";
		$cc['DE'] = "Germany";
		$cc['GH'] = "Ghana";
		$cc['GI'] = "Gibraltar";
		$cc['GR'] = "Greece";
		$cc['GL'] = "Greenland";
		$cc['GD'] = "Grenada";
		$cc['GP'] = "Guadeloupe";
		$cc['GU'] = "Guam";
		$cc['GT'] = "Guatemala";
		$cc['GN'] = "Guinea";
		$cc['GW'] = "Guinea-Bissau";
		$cc['GY'] = "Guyana";
		$cc['HT'] = "Haiti";
		$cc['VA'] = "Vatican";
		$cc['HN'] = "Honduras";
		$cc['HK'] = "Hong kong";
		$cc['HU'] = "Hungary";
		$cc['IS'] = "Iceland";
		$cc['IN'] = "India";
		$cc['ID'] = "Indonesia";
		$cc['IR'] = "Iran";
		$cc['IQ'] = "Iraq";
		$cc['IE'] = "Ireland";
		$cc['IL'] = "Israel";
		$cc['IT'] = "Italy";
		$cc['JM'] = "Jamaica";
		$cc['JP'] = "Japan";
		$cc['JO'] = "Jordan";
		$cc['KZ'] = "Kazakhstan";
		$cc['KE'] = "Kenya";
		$cc['KI'] = "Kiribati";
		$cc['KR'] = "Korea";
		$cc['KW'] = "Kuwait";
		$cc['KG'] = "Kyrgyzstan";
		$cc['LV'] = "Latvia";
		$cc['LB'] = "Lebanon";
		$cc['LS'] = "Lesotho";
		$cc['LR'] = "Liberia";
		$cc['LI'] = "Liechtenstein";
		$cc['LT'] = "Lithuania";
		$cc['LU'] = "Luxembourg";
		$cc['MO'] = "Macau";
		$cc['MG'] = "Madagascar";
		$cc['MW'] = "Malawi";
		$cc['MY'] = "Malaysia";
		$cc['MV'] = "Maldives";
		$cc['ML'] = "Mali";
		$cc['MT'] = "Malta";
		$cc['MH'] = "Marshall Islands";
		$cc['MQ'] = "Martinique";
		$cc['MR'] = "Mauritania";
		$cc['MU'] = "Mauritius";
		$cc['YT'] = "Mayotte";
		$cc['MX'] = "Mexico";
		$cc['FM'] = "Micronesia";
		$cc['MD'] = "Moldova";
		$cc['MC'] = "Monaco";
		$cc['MN'] = "Mongolia";
		$cc['MS'] = "Montserrat";
		$cc['MA'] = "Morocco";
		$cc['MZ'] = "Mozambique";
		$cc['MM'] = "Myanmar";
		$cc['NA'] = "Namibia";
		$cc['NR'] = "Nauru";
		$cc['NP'] = "Nepal";
		$cc['NL'] = "Netherlands";
		$cc['AN'] = "Netherlands Antilles";
		$cc['NC'] = "New Caledonia";
		$cc['NZ'] = "New Zealand";
		$cc['NI'] = "Nicaragua";
		$cc['NE'] = "Niger";
		$cc['NG'] = "Nigeria";
		$cc['NU'] = "Niue";
		$cc['NF'] = "Norfolk Island";
		$cc['NO'] = "Norway";
		$cc['OM'] = "Oman";
		$cc['PK'] = "Pakistan";
		$cc['PW'] = "Palau";
		$cc['PA'] = "Panama";
		$cc['PG'] = "Papua New Guinea";
		$cc['PY'] = "Paraguay";
		$cc['PE'] = "Peru";
		$cc['PH'] = "Philippines";
		$cc['PN'] = "Pitcairn";
		$cc['PL'] = "Poland";
		$cc['PT'] = "Portugal";
		$cc['PR'] = "Puerto Rico";
		$cc['QA'] = "Qatar";
		$cc['RE'] = "Reunion";
		$cc['RO'] = "Romania";
		$cc['RU'] = "Russia";
		$cc['RW'] = "Rwanda";
		$cc['KN'] = "Saint Kitts and Nevis";
		$cc['LC'] = "Saint Lucia";
		$cc['WS'] = "Samoa";
		$cc['SM'] = "San Marino";
		$cc['SA'] = "Saudi Arabia";
		$cc['SN'] = "Senegal";
		$cc['SC'] = "Seychelles";
		$cc['SL'] = "Sierra Leone";
		$cc['SG'] = "Singapore";
		$cc['SK'] = "Slovakia";
		$cc['SI'] = "Slovenia";
		$cc['SB'] = "Solomon Islands";
		$cc['SO'] = "Somalia";
		$cc['ZA'] = "South Africa";
		$cc['GS'] = "South Georgia";
		$cc['ES'] = "Spain";
		$cc['LK'] = "Sri Lanka";
		$cc['SH'] = "St. Helena";
		$cc['SD'] = "Sudan";
		$cc['SR'] = "Suriname";
		$cc['SZ'] = "Swaziland";
		$cc['SE'] = "Sweden";
		$cc['CH'] = "Switzerland";
		$cc['SY'] = "Syrian Arab Republic";
		$cc['TW'] = "Taiwan";
		$cc['TJ'] = "Tajikistan";
		$cc['TZ'] = "Tanzania";
		$cc['TH'] = "Thailand";
		$cc['TG'] = "Togo";
		$cc['TK'] = "Tokelau";
		$cc['TO'] = "Tonga";
		$cc['TT'] = "Trinidad and Tobago";
		$cc['TN'] = "Tunisia";
		$cc['TR'] = "Turkey";
		$cc['TM'] = "Turkmenistan";
		$cc['TV'] = "Tuvalu";
		$cc['UG'] = "Uganda";
		$cc['UA'] = "Ukraine";
		$cc['AE'] = "United Arab Emirates";
		$cc['US'] = "United States";
		$cc['GB'] = "United Kingdom";
		$cc['UY'] = "Uruguay";
		$cc['UZ'] = "Uzbekistan";
		$cc['VU'] = "Vanuatu";
		$cc['VE'] = "Venezuela";
		$cc['VN'] = "Viet Nam";
		$cc['VI'] = "Virgin Islands";
		$cc['EH'] = "Western Sahara";
		$cc['YE'] = "Yemen";
		$cc['YU'] = "Yugoslavia";
		$cc['ZM'] = "Zambia";
		$cc['ZW'] = "Zimbabwe";
		return $cc;
	}

	public static function getRegions()
	{
		$vals = array();
		$vals['Mid Atlantic'] = "MA";
		$vals['Midwest'] = "MW";
		$vals['Great Lakes'] = "GL";
		$vals['North'] = "N";
		$vals['Northeast'] = "NE";
		$vals['Pacific Northwest'] = "PNW";
		$vals['South'] = "S";
		$vals['Southeast'] = "SE";
		$vals['Southwest'] = "SW";
		$vals['Pacific Southwest'] = "PSW";
		$vals['Central'] = "C";
		return $vals;
	}

	public static function getTargetAge()
	{
		$vals = array();
		$vals['0 - 3 years'] = 1;
		$vals['4 - 8 years'] = 4;
		$vals['9 - 12 years'] = 9;
		$vals['13 - 17 years'] = 13;
		$vals['18 - 24 years'] = 18;
		$vals['25 - 34 years'] = 25;
		$vals['35 - 44 years'] = 33;
		$vals['45 - 54 years'] = 45;
		$vals['55+ years'] = 55;
		return $vals;
	}
    public static function ccCodeToCountryName($cc)
    {
        switch ($cc)
        {
            case "US": return "United States";
            case "AF": return "Afghanistan";
            case "AL": return "Albania";
            case "DZ": return "Algeria";
            case "AS": return "American samoa";
            case "AD": return "Andorra";
            case "AO": return "Angola";
            case "AI": return "Anguilla";
            case "AQ": return "Antarctica";
            case "AG": return "Antigua and Barbuda";
            case "AR": return "Argentina";
            case "AM": return "Armenia";
            case "AW": return "Aruba";
            case "AU": return "Australia";
            case "AT": return "Austria";
            case "AZ": return "Azerbaijan";
            case "BS": return "Bahamas";
            case "BH": return "Bahrain";
            case "BD": return "Bangladesh";
            case "BB": return "Barbados";
            case "BY": return "Belarus";
            case "BE": return "Belgium";
            case "BZ": return "Belize";
            case "BJ": return "Benin";
            case "BM": return "Bermuda";
            case "BT": return "Bhutan";
            case "BO": return "Bolivia";
            case "BA": return "Bosnia";
            case "BW": return "Botswana";
            case "BV": return "Bouvet Island";
            case "BR": return "Brazil";
            case "BN": return "Brunei Darussalam";
            case "BG": return "Bulgaria";
            case "BF": return "Burkina Faso";
            case "BI": return "Burundi";
            case "KH": return "Cambodia";
            case "CM": return "Cameroon";
            case "CA": return "Canada";
            case "CV": return "Cape Verde";
            case "KY": return "Cayman Islands";
            case "TD": return "Chad";
            case "CL": return "Chile";
            case "CN": return "China";
            case "CX": return "Christmas island";
            case "CO": return "Colombia";
            case "KM": return "Comoros";
            case "CG": return "Congo";
            case "CK": return "Cook Islands";
            case "CR": return "Costa Rica";
            case "CI": return "Cote D'ivoire";
            case "HR": return "Croatia (Hrvatska)";
            case "CU": return "Cuba";
            case "CY": return "Cyprus";
            case "CZ": return "Czech Republic";
            case "DK": return "Denmark";
            case "DJ": return "Djibouti";
            case "DM": return "Dominica";
            case "DO": return "Dominican Republic";
            case "TP": return "East timor";
            case "EC": return "Ecuador";
            case "EG": return "Egypt";
            case "SV": return "El Salvador";
            case "GQ": return "Equatorial Guinea";
            case "ER": return "Eritrea";
            case "EE": return "Estonia";
            case "ET": return "Ethiopia";
            case "FO": return "Faroe Islands";
            case "FJ": return "Fiji";
            case "FI": return "Finland";
            case "FR": return "France";
            case "FX": return "France, Metropolitan";
            case "GF": return "French Guiana";
            case "PF": return "French Polynesia";
            case "GA": return "Gabon";
            case "GM": return "Gambia";
            case "GE": return "Georgia";
            case "DE": return "Germany";
            case "GH": return "Ghana";
            case "GI": return "Gibraltar";
            case "GR": return "Greece";
            case "GL": return "Greenland";
            case "GD": return "Grenada";
            case "GP": return "Guadeloupe";
            case "GU": return "Guam";
            case "GT": return "Guatemala";
            case "GN": return "Guinea";
            case "GW": return "Guinea-Bissau";
            case "GY": return "Guyana";
            case "HT": return "Haiti";
            case "VA": return "Vatican";
            case "HN": return "Honduras";
            case "HK": return "Hong kong";
            case "HU": return "Hungary";
            case "IS": return "Iceland";
            case "IN": return "India";
            case "ID": return "Indonesia";
            case "IR": return "Iran";
            case "IQ": return "Iraq";
            case "IE": return "Ireland";
            case "IL": return "Israel";
            case "IT": return "Italy";
            case "JM": return "Jamaica";
            case "JP": return "Japan";
            case "JO": return "Jordan";
            case "KZ": return "Kazakhstan";
            case "KE": return "Kenya";
            case "KI": return "Kiribati";
            case "KR": return "Korea";
            case "KW": return "Kuwait";
            case "KG": return "Kyrgyzstan";
            case "LV": return "Latvia";
            case "LB": return "Lebanon";
            case "LS": return "Lesotho";
            case "LR": return "Liberia";
            case "LI": return "Liechtenstein";
            case "LT": return "Lithuania";
            case "LU": return "Luxembourg";
            case "MO": return "Macau";
            case "MG": return "Madagascar";
            case "MW": return "Malawi";
            case "MY": return "Malaysia";
            case "MV": return "Maldives";
            case "ML": return "Mali";
            case "MT": return "Malta";
            case "MH": return "Marshall Islands";
            case "MQ": return "Martinique";
            case "MR": return "Mauritania";
            case "MU": return "Mauritius";
            case "YT": return "Mayotte";
            case "MX": return "Mexico";
            case "FM": return "Micronesia";
            case "MD": return "Moldova";
            case "MC": return "Monaco";
            case "MN": return "Mongolia";
            case "MS": return "Montserrat";
            case "MA": return "Morocco";
            case "MZ": return "Mozambique";
            case "MM": return "Myanmar";
            case "NA": return "Namibia";
            case "NR": return "Nauru";
            case "NP": return "Nepal";
            case "NL": return "Netherlands";
            case "AN": return "Netherlands Antilles";
            case "NC": return "New Caledonia";
            case "NZ": return "New Zealand";
            case "NI": return "Nicaragua";
            case "NE": return "Niger";
            case "NG": return "Nigeria";
            case "NU": return "Niue";
            case "NF": return "Norfolk Island";
            case "NO": return "Norway";
            case "OM": return "Oman";
            case "PK": return "Pakistan";
            case "PW": return "Palau";
            case "PA": return "Panama";
            case "PG": return "Papua New Guinea";
            case "PY": return "Paraguay";
            case "PE": return "Peru";
            case "PH": return "Philippines";
            case "PN": return "Pitcairn";
            case "PL": return "Poland";
            case "PT": return "Portugal";
            case "PR": return "Puerto Rico";
            case "QA": return "Qatar";
            case "RE": return "Reunion";
            case "RO": return "Romania";
            case "RU": return "Russia";
            case "RW": return "Rwanda";
            case "KN": return "Saint Kitts and Nevis";
            case "LC": return "Saint Lucia";
            case "WS": return "Samoa";
            case "SM": return "San Marino";
            case "SA": return "Saudi Arabia";
            case "SN": return "Senegal";
            case "SC": return "Seychelles";
            case "SL": return "Sierra Leone";
            case "SG": return "Singapore";
            case "SK": return "Slovakia";
            case "SI": return "Slovenia";
            case "SB": return "Solomon Islands";
            case "SO": return "Somalia";
            case "ZA": return "South Africa";
            case "GS": return "South Georgia";
            case "ES": return "Spain";
            case "LK": return "Sri Lanka";
            case "SH": return "St. Helena";
            case "SD": return "Sudan";
            case "SR": return "Suriname";
            case "SZ": return "Swaziland";
            case "SE": return "Sweden";
            case "CH": return "Switzerland";
            case "SY": return "Syrian Arab Republic";
            case "TW": return "Taiwan";
            case "TJ": return "Tajikistan";
            case "TZ": return "Tanzania";
            case "TH": return "Thailand";
            case "TG": return "Togo";
            case "TK": return "Tokelau";
            case "TO": return "Tonga";
            case "TT": return "Trinidad and Tobago";
            case "TN": return "Tunisia";
            case "TR": return "Turkey";
            case "TM": return "Turkmenistan";
            case "TV": return "Tuvalu";
            case "UG": return "Uganda";
            case "UA": return "Ukraine";
            case "AE": return "United Arab Emirates";
            case "GB": return "United Kingdom";
            case "UY": return "Uruguay";
            case "UZ": return "Uzbekistan";
            case "VU": return "Vanuatu";
            case "VE": return "Venezuela";
            case "VN": return "Viet Nam";
            case "VI": return "Virgin Islands";
            case "EH": return "Western Sahara";
            case "YE": return "Yemen";
            case "YU": return "Yugoslavia";
            case "ZM": return "Zambia";
            case "ZW": return "Zimbabwe";
          default: throw new UnrecoverableSystemException("Invalid Input", "Country code is invalid.");
        }
    }
}

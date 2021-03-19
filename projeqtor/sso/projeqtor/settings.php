<?php

$spBaseUrl=SSO::getSettingValue('spBaseUrl');
$entityId=SSO::getSettingValue('entityId');
$idpCert=SSO::getSettingValue('idpCert');
$singleSignOnServiceUrl=SSO::getSettingValue('singleSignOnServiceUrl'); 
$singleLogoutServiceUrl=SSO::getSettingValue('singleLogoutServiceUrl'); 
$idpEntityId=SSO::getSettingValue('idpEntityId'); 
$lowerCaseEncoding=false;
$isADFS=SSO::getSettingValue('isADFS'); 
if ($isADFS) $lowerCaseEncoding=true;
$technicalContactName=SSO::getSettingValue('technicalContactName');
$technicalContactEmail=SSO::getSettingValue('technicalContactEmail');
		
$settingsInfo = array (
  'baseurl' => $spBaseUrl,
  'strict' => false, // If 'strict' is True, then will reject unsigned or unencrypted messages or messages if not strictly follow the SAML standard
  'debug' => false,
	'sp' => array (
		'entityId' => $entityId,
		'assertionConsumerService' => array (
			'url' => $spBaseUrl.'/projeqtor/index.php?acs',
		),
		'singleLogoutService' => array (
			'url' => $spBaseUrl.'/projeqtor/index.php?sls',
		),
		'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
	),
	'idp' => array (
		'entityId' => $idpEntityId,
		'singleSignOnService' => array (
			'url' => $singleSignOnServiceUrl,
		),
		'singleLogoutService' => array (
			'url' => $singleLogoutServiceUrl,
		),
		'x509cert' => $idpCert,
	),
  'compress' => array (
      'requests' => true,
      'responses' => true
  ),
  'security' => array (
      'nameIdEncrypted' => false,
      'authnRequestsSigned' => false,
      'logoutRequestSigned' => false,
      'logoutResponseSigned' => false,
      'signMetadata' => false,
      'wantMessagesSigned' => false,
      'wantAssertionsEncrypted' => false,
      'wantAssertionsSigned' => false,
      'wantNameId' => true,
      'wantNameIdEncrypted' => false,
      'requestedAuthnContext' => false,
      'requestedAuthnContextComparison' => 'exact',
      'wantXMLValidation' => true,
      'relaxDestinationValidation' => true, //false,
      'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
      'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
      
      'lowercaseUrlencoding' => $lowerCaseEncoding, // ADFS URL-Encodes SAML data as lowercase, and the toolkit by default uses uppercase. Turn it True for ADFS compatibility on signature verification
  ),

    // Contact information template, it is recommended to suply a technical and support contacts
    'contactPerson' => array (
        'technical' => array (
            'givenName' => $technicalContactName,
            'emailAddress' => $technicalContactEmail
        ),
    ),
);

<?php
 
/**
 *  SAML Metadata view
 */
// For PROJEQTOR compatibility
chdir('..');
$maintenance=true; // For projeqtor to avoid connection on 
require_once dirname(dirname(__DIR__)).'/tool/projeqtor.php';
// SAML Settings
require_once dirname(__DIR__).'/_toolkit_loader.php';
require_once 'projeqtor/settings.php' ;

try {
    $settings = new OneLogin_Saml2_Settings($settingsInfo, true);
    $metadataValidityDuration=86400*365.25*10; // 10 years
    $validUntil =  time() + $metadataValidityDuration;
    $metadata = $settings->getSPMetadata(false,$validUntil);
    $errors = $settings->validateMetadata($metadata);
    if (empty($errors)) {
        header('Content-Type: text/xml');
        echo $metadata;
    } else {
        throw new OneLogin_Saml2_Error(
            'Invalid SP metadata: '.implode(', ', $errors),
            OneLogin_Saml2_Error::METADATA_SP_INVALID
        );
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

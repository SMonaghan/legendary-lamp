<?php

use Aws\Credentials\CredentialProvider;
use Aws\SecretsManager\SecretsManagerClient;
use Aws\Exception\AwsException;

function index($data)
{

    echo "here\n";
    $provider = CredentialProvider::defaultProvider();

    $client = new SecretsManagerClient( [
        'credentials' => $provider,
        'version' => 'latest',
        'region' => 'us-east-1'
    ] );
    echo "here 2\n";
    $secretName = 'test';

    try {
        $result = $client->getSecretValue( [
            'SecretId' => $secretName,
        ] );
    } catch ( AwsException $e ) {
        $error = $e->getAwsErrorCode();
        if ( $error == 'DecryptionFailureException' ) { // Can't decrypt the protected secret text using the provided AWS KMS key.
            throw $e;
        }
        if ( $error == 'InternalServiceErrorException' ) { // An error occurred on the server side.
            throw $e;
        }
        if ( $error == 'InvalidParameterException' ) { // Invalid parameter value.
            throw $e;
        }
        if ( $error == 'InvalidRequestException' ) { // Parameter value is not valid for the current state of the resource.
            throw $e;
        }
        if ( $error == 'ResourceNotFoundException' ) { // Requested resource not found
            throw $e;
        }
    }
    echo "here 3\n";
    // Decrypts secret using the associated KMS CMK, depends on whether the secret is a string or binary.
    if ( isset( $result[ 'SecretString' ] ) ) {
        $secret = $result[ 'SecretString' ];
    } else {
        $secret = base64_decode( $result[ 'SecretBinary' ] );
    }

    // Decode the secret json
    $secrets = json_decode( $secret, true );

echo( '<p>hostname/ipaddress: ' . $secrets[ 'host' ] . '</p><p>username: ' . $secrets[ 'username' ] . '</p><p>password: ' . $secrets[ 'password' ] . '</p><p>dbname: ' . $secrets[ 'dbname' ] . '</p>' );
}
?>

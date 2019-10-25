Passman offers a api for extensions.


## Table of Contents
- [Authentication](#authentication)
- [Get vaults](#get-vaults-get)
- [Get vault](#get-vault-get)
- [Create new credential](#create-new-credential-post)
- [Update credential](#update-credential-patch)
- [Decrypting Credentials / challenge password ](#decrypting-credentials--challenge-password)


## Authentication

All apps must authenticate.
For example in JS it would be:

```
var encodedLogin ="MyUsername:MyPassword";
var request = new XMLHttpRequest({"mozAnon": true});
request.setRequestHeader("Authorization", "Basic " + encodedLogin);
request.setRequestHeader("Content-Type", "application/json");
```

An other option is logging in via HTTP Basic auth.
In this case an example would be:
`https://MyUsername:Mypassword@nextcloudinstance.com`

Connectivity via http is possible, but you *MUST* warn that their login credentials are send in plaintext.
The credentials from Passman are still send encrypted if http is used.


### Get vaults [GET]

`/apps/passman/api/v2/vaults`

This will return a list of vaults.
A vault consists of the following properties:

```
{
    "vault_id": 17,
    "guid": "64DDADA1-54A6-4BE6-AA2F-BCB2EC8E8455",
    "name": "test",
    "created": 1484175865,
    "public_sharing_key": "-----BEGIN PUBLIC KEY-----\r\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC1h6j+vLcvJDUgOi6VkjzDKTT0\r\nLXluie7+VH2DjnzeXO2QalHI1qAzd\/G51r2NArgwzKMm9g\/kGN1V+mcX3j2WZu\/E\r\n8o5jk83LaSlgcG9GIbOyXUXJlflvctnhPa8Em3GoM\/ZfO2EkkDYANTKvyiyRXroa\r\ny6m2C+aJVzxmhj5tvQIDAQAB\r\n-----END PUBLIC KEY-----\r\n",
    "last_access": 1484216598,
    "challenge_password": "eyJpdiI6IkFEWExocDFsRWFZSEZhc0cxY2NzUnciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiJFVmdZLzIxNmI0USIsImN0IjoiU3d5QUkzdVFqenh1cStwaCJ9"
}
```

Short description of the fields:
- `vault_id` - Id of the vault, only used within queries.
- `vault_guid` - The guid of the vault, use this when making requests.
- `name` - The name of the vault.
- `created` - Timestamp when the vault was created.
- `public_sharing_key` - The public sharing key.
- `last_access` - Timestamp when the vault was last accessed.
- `challenge_password` - Encrypted challenge password, you can use this to check if the user provided a correct password.


### Get vault [GET]

`/apps/passman/api/v2/vaults/{vault_guid}`

To request the credentials.
This will return the requested vault and it's credentials:

```$xslt
created: 1484175865
credentials: [{}, {}, ....]
guid: "64DDADA1-54A6-4BE6-AA2F-BCB2EC8E8455"
last_access: 1484217620
name: "test"
private_sharing_key ''
public_sharing_key: ''
sharing_keys_generated: 1484175865
vault_id: 17
vault_settings: null
```

To see how a credential is build up (which fields), see [create new credential](#Create new credential).


### Create new credential [POST]

`/api/v2/credentials`

Fields:

```$xslt
var credential = {
    'vault_id': int,
    'label': string,
    'description': string,
    'created': null (Will be set server side),
    'changed': null (Will be set server side),
    'tags': [{text: string}],
    'email': string,
    'username': string,
    'password': string (encrypted),
    'url': string (encrypted),
    'favicon': string,
    'renew_interval': int,
    'expire_time': timestamp,
    'delete_time': timestamp,
    'files': [
        {
            filename:  string,
            size: int (size in bytes),
            mimetype: string,
            guid: string (generated server side)
        }
    ],
    'custom_fields': [
        {
            label: string,
            value: string,
            secret: bool,
            field_type: 'text'
        }
    ],
    'otp': {},
    'hidden': false
};
```

There are a few special fields here.

- `custom_fields`
    - Those fields are added by the user `secret` indicates if the value should be hidden.

When posting to the endpoint the following fields are required:
- `label`
- `vault_id`


### Update credential [PATCH]

`/api/v2/credentials/{credential_guid}`
See [create new credential](#Create new credential).


### Decrypting Credentials / challenge password

For the client side encryption we use [sjcl](https://github.com/bitwiseshiftleft/sjcl).
To decrypt (and test if a valid key is given):

```$xslt
var encryption_config = {
    adata: "",
    iter: 1000,
    ks: 256,
    mode: 'ccm',
    ts: 64
};
var ciphertext = window.atob(encryptedString);
var rp = {};
try {
    return sjcl.decrypt(_key, ciphertext, encryption_config, rp);
} catch (e) {
    throw e; // Invalid key
}
```

For decrypting the credentials you can use above code.
The following fields are encrypted:
- `description`
- `username`
- `password`
- `files`
- `custom_fields`
- `otp`
- `email`
- `tags`
- `url`


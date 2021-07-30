# DocuSign for Laravel 8

<dl>
  <dt>This package was developed to utilize e-contract/signatures directly within a Laravel based CRM. </dt>
</dl>

[![PHPPackages Rank](http://phppackages.org/p/tjphippen/docusign/badge/rank.svg)](http://phppackages.org/p/tjphippen/docusign)
[![Latest Stable Version](https://poser.pugx.org/tjphippen/docusign/v/stable.png)](https://packagist.org/packages/tjphippen/docusign) [![Total Downloads](https://poser.pugx.org/tjphippen/docusign/downloads.png)](https://packagist.org/packages/tjphippen/docusign)
- [Packagist](https://packagist.org/packages/tjphippen/docusign)
- [GitHub](https://github.com/tjphippen/docusign)


### Refer to 
[Latest Docusign API Documentation](https://developers.docusign.com/docs/esign-rest-api/reference/) for outdated links.

Also see my [eOriginal](https://github.com/tjphippen/eoriginal) package


----------
## Installation
Add the following to your `composer.json` file.

~~~
"tjphippen/docusign": "0.4*@dev"
~~~

Then run `composer install` or `composer update` to download and install.

You'll then need to register the service provider in your `config/app.php` file within `providers`.

```php
'providers' => array(
    Tjphippen\Docusign\DocusignServiceProvider::class,
)
```

DocuSign includes a auto registered facade which provides the static syntax for managing envelopes, recipients etc. 
If you have issues simply add it manually to your aliases array

```php
'aliases' => array(
    'Docusign'  => Tjphippen\Docusign\Facades\Docusign::class,
)
```

### Create configuration file using artisan

```
$ php artisan vendor:publish
```

The configuration file will be published to `config/docusign.php` which must be completed to make connections to the API.


```php

    /**
     * The DocuSign Integrator's Key
     */

    'integrator_key' => '',

    /**
     * The Docusign Account Email
     */
    'email' => '',

    /**
     * The Docusign Account Password
     */
    'password' => '',
...
```

## Examples

#### Get List of Users

```php
Docusign::getUsers();
```

#### Get Individual User

```php
Docusign::getUser($userId); 
Docusign::getUser($userId, true);  // When true, the full list of user information is returned for the user. 
```

#### Get Folders

```php
Docusign::getFolders(); // By default only the list of template folders are returned
Docusign::getFolders(true);  // Will return normal folders plus template folders
```

#### Get Folder Envelope List

```php
Docusign::getFolderEnvelopes($folderId);
```
See: [All Parameters](https://www.docusign.com/p/RESTAPIGuide/RESTAPIGuide.htm#REST%20API%20References/Get%20Folder%20Envelope%20List.htm%3FTocPath%3DREST%2520API%2520References%7C_____97) for this method.
```php
Docusign::getFolderEnvelopes($folderId, array(
   'start_position' => 1, // Integer
   'from_date' => '', // date/Time
   'to_date' => '', // date/Time
   'search_text' => '', // String
   'status' => 'created', // Status
   'owner_name' => '', // username
   'owner_email' => '', // email
   );
```

#### Get List of Templates

```php
Docusign::getTemplates();
```
Or with [Additional Parameters](https://www.docusign.com/p/RESTAPIGuide/RESTAPIGuide.htm#REST%20API%20References/Get%20List%20of%20Templates.htm%3FTocPath%3DREST%2520API%2520References%7C_____115).
```php
Docusign::getTemplates(array(
   'folder' => 1, // String (folder name or folder ID)
   'folder_ids' => '', // Comma separated list of folder ID GUIDs.
   'include' => '', // Comma separated list of additional template attributes
    ...
   );
```

#### Get Template

```php
Docusign::getTemplate($templateId);
```

#### Get Multiple Envelopes
```php
$envelopes = array('49d91fa5-1259-443f-85fc-708379fd7bbe', '8b2d44a-41dc-4698-9233-4be0678c345c');
Docusign::getEnvelopes($envelopes);
```

#### Get Individual Envelope

```php
Docusign::getEnvelope($envelopeId);
```

#### Get Envelope Recipient

```php
Docusign::getEnvelopeRecipients($envelopeId);
```
To include tabs simply set the second parameter to true:

```php
Docusign::getEnvelopeRecipients($envelopeId, true);
```

#### Get Envelope Custom Fields

```php
Docusign::getEnvelopeCustomFields($envelopeId);
```

#### Get Tab Information for a Recipient
See: [Tab Parameters](https://www.docusign.com/p/RESTAPIGuide/RESTAPIGuide.htm#REST%20API%20References/Tab%20Parameters.htm%3FTocPath%3DREST%2520API%2520References%7CSend%2520an%2520Envelope%2520or%2520Create%2520a%2520Draft%2520Envelope%7CTab%2520Parameters%7C_____0)


```php
Docusign::getEnvelopeTabs($envelopeId, $recipientId);
```

#### Modify Tabs for a Recipient
This one is a bit tricky. The `tabId` is required and must be within set of arrays.
See: [Tab Types and Parameters] (https://www.docusign.com/p/RESTAPIGuide/RESTAPIGuide.htm#REST%20API%20References/Tab%20Parameters.htm)
```php
$tabs = ['textTabs' => [['tabId' => '270269f6-4a84-4ff9-86db-2a572eb73d99', 'value' => '123 Fake Street']]];
Docusign::updateRecipientTabs($envelopeId, $recipientId, $tabs);
```

#### Create/Send an Envelope from a Template


See: [Send an Envelope or Create a Draft Envelope](https://www.docusign.com/p/RESTAPIGuide/RESTAPIGuide.htm#REST%20API%20References/Send%20an%20Envelope.htm%3FTocPath%3DREST%2520API%2520References%7CSend%2520an%2520Envelope%2520or%2520Create%2520a%2520Draft%2520Envelope%7C_____0) for full list of parameters/options.

```php
Docusign::createEnvelope(array(
   'templateId'     => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', // Template ID
   'emailSubject'   => 'Demo Envelope Subject', // Subject of email sent to all recipients
   'status'         => 'created', // created = draft ('sent' will send the envelope!)
   'templateRoles'  => array(
        ['name'     => 'TJ Phippen',
         'email'    => 'tj@tjphippen.com',
         'roleName' => 'Contractor',
         'clientUserId'  => 1],
        ['name'     => 'Jane Someone',
         'email'    => 'demo@demo.com',
         'roleName' => 'Customer']),
    ));
```

#### Modify Draft Envelope Email Subject and Message

The `updateEnvelope` method can be used in a variety of ways..

```php
Docusign::updateEnvelope($envelopeId, array(
    'emailSubject' => 'New Email Subject', // Required
    'emailBlurb' => 'Email message body text'
));
```

#### Post Recipient View

Returns embeded signing URL. [Reference] (https://www.docusign.com/p/RESTAPIGuide/RESTAPIGuide.htm#REST%20API%20References/Post%20Recipient%20View.htm)

```php
Docusign::createRecipientView($envelopeId, array(
    'userName' => 'TJ Phippen',
    'email' => 'tj@tjphippen.com',
    'AuthenticationMethod' => 'email',
    'clientUserId' => 1, // Must create envelope with this ID
    'returnUrl' => 'http://your-site.tdl/returningUrl'
));
```

#### Send Draft Envelope

```php
Docusign::updateEnvelope($envelopeId, ['status' => 'sent']);
```

#### Void Envelope

```php
Docusign::updateEnvelope($envelopeId, array(
    'status' => 'voided',
    'voidedReason' => 'Just Testing'
));
```

#### Delete Envelope

```php
Docusign::deleteEnvelope($envelopeId);
```


## Change Log

#### v0.2.0

- Updated Guzzle dependancy & namespace

#### v0.2.0

- Added trait

#### v0.1.0

- Released

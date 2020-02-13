<?php namespace Tjphippen\Docusign;

use GuzzleHttp\Client;

class Docusign
{
    private $config;
    private $client;
    private $baseUrl;

    function __construct($config, $clientSettings=[])
    {
        $this->config = $config;
        $this->baseUrl = 'https://' . $config['environment']. '.docusign.net/restapi/' . $config['version'] . '/accounts/' . $config['account_id'] . '/';
        if(array_key_exists('query', $clientSettings)) unset($clientSettings['query']); //don't let some malicious user somehow override our request bodies. 
        if(array_key_exists('json', $clientSettings)) unset($clientSettings['json']); //they'd be overwritten anyway, but still.
        $this->client = new Client(array_merge($clientSettings, ['base_uri' => $this->baseUrl, 'headers' => $this->getHeaders()]));
    }

    public function getUsers()
    {
        $request = $this->client->get('users');
        $users = $this->rawJson($request);
        return $users['users'];
    }

    public function getUser($userId, $additional_info = false)
    {
        $additional_info = ($additional_info) ? 'true' : 'false';
        $request = $this->client->get('users/' . $userId . '?additional_info=' . $additional_info);
        return $user = $this->rawJson($request);
    }

    public function getEnvelopes($envelopeIds)
    {
        $envelopes = array('envelopeIds' => $envelopeIds);
        $request = $this->client->put('envelopes/status', ['json' => $envelopes, 'query' => ['envelope_ids' => 'request_body']]);
        return $envelopes = $this->rawJson($request);
    }

    public function getEnvelope($envelopeId)
    {
        $request = $this->client->get('envelopes/' . $envelopeId);
        return $envelope = $this->rawJson($request);
    }

    public function getEnvelopePdf($envelopeId)
    {
        $request = $this->client->get('envelopes/' . $envelopeId . '/documents/combined?certificate=true');
        return $request->getBody()->getContents();
    }

    public function getEnvelopeRecipients($envelopeId, $include_tabs = false)
    {
        $include_tabs = ($include_tabs) ? 'true' : 'false';
        $request = $this->client->get('envelopes/' . $envelopeId . '/recipients?include_tabs=' . $include_tabs);
        return $recipients = $this->rawJson($request);
    }

    public function getEnvelopeTabs($envelopeId, $recipientId)
    {
        $request = $this->client->get('envelopes/' . $envelopeId . '/recipients/' . $recipientId . '/tabs');
        return $tabs = $this->rawJson($request);
    }

    public function createEnvelope($data) {
        $request = $this->client->post('envelopes/', ['json' => $data]);
        return $envelope = $this->rawJson($request);
    }

    public function updateEnvelope($envelopeId, $data)
    {
        $request = $this->client->put('envelopes/' . $envelopeId, ['json' => $data]);
        return $envelope = $this->rawJson($request);
    }

    public function updateRecipientTabs($envelopeId, $recipientId, $tabs)
    {
        $request = $this->client->put('envelopes/' . $envelopeId . '/recipients/' . $recipientId . '/tabs', ['json' => $tabs]);
        return $tabs = $this->rawJson($request);
    }

    public function deleteEnvelope($envelopeId) {
        $data = array('envelopeIds' => array($envelopeId));
        $request = $this->client->put('folders/recyclebin', ['json' => $data]);
        return $deleted = $this->rawJson($request);
    }

    public function getTemplates($options = null)
    {
        $request = $this->client->get('templates', ['query' => $options]);
        $templates = $this->rawJson($request);
        return $templates['envelopeTemplates'];
    }

    public function getTemplate($templateId)
    {
        $request = $this->client->get('templates/' . $templateId);
        return $template = $this->rawJson($request);
    }

    public function getEnvelopeTemplates($envelopeId)
    {
        $request = $this->client->get('envelopes/' . $envelopeId . '/templates');
        $templates = $this->rawJson($request);
        return $templates['templates'];
    }

    public function getFolders($templates = false)
    {
        $templates = ($templates) ? 'include' : 'only';
        $request = $this->client->get('folders/?template=' . $templates);
        $folders = $this->rawJson($request);
        return $folders['folders'];
    }

    public function getFolderEnvelopes($folderId, $options = null)
    {
        $request = $this->client->get('folders/' . $folderId, ['query' => $options]);
        $envelopes = $this->rawJson($request);
        return $envelopes;
    }

    public function getEnvelopeCustomFields($envelopeId)
    {
        $request = $this->client->get('envelopes/' . $envelopeId . '/custom_fields');
        return $custom_fields = $this->rawJson($request);
    }

    public function createRecipientView($envelopeId, $data)
    {
        $request = $this->client->post('envelopes/' . $envelopeId . '/views/recipient', ['json' => $data]);
        return $view = $this->rawJson($request);
    }

    public function updateEnvelopeDocuments($envelopeId, $data)
    {
        $request = $this->client->put('envelopes/' . $envelopeId . '/documents', ['json' => $data]);
        return $view = $this->rawJson($request);
    }

    // Helper Functions
    public function rawJson($response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    public function getHeaders($accept = 'application/json', $contentType = 'application/json')
    {
        return array(
            'X-DocuSign-Authentication' => '<DocuSignCredentials><Username>' . $this->config['email'] . '</Username><Password>' . $this->config['password'] . '</Password><IntegratorKey>' . $this->config['integrator_key'] . '</IntegratorKey></DocuSignCredentials>',
            'Accept' => $accept,
            'Content-Type' => $contentType
        );
    }
}

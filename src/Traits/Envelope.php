<?php namespace Tjphippen\Docusign\Traits;

use Docusign;
use Carbon\Carbon;

trait Envelope {

    public function envelope()
    {
        return Docusign::getEnvelope($this->getEnvelopeId());
    }

    public function recipients($includeTabs = false)
    {
        return Docusign::getEnvelopeRecipients($this->getEnvelopeId(), $includeTabs);
    }

    public function tabs($recipientId = null, $update = false)
    {
        $tabs = $this->{config('docusign.tabs_field')};
        if($update || !isset($tabs[$recipientId])){
            $tabs[$recipientId] = Docusign::getRecipientTabs($this->getEnvelopeId(), $recipientId);
            $this->update([config('docusign.tabs_field') => $tabs]);
        }
        return $recipientId ? $tabs[$recipientId] : $tabs;
    }

    private function getEnvelopeId()
    {
        return $this->{config('docusign.envelope_field')};
    }

    public function transformTabs($recipientId, $data)
    {
        foreach($this->tabs($recipientId) as $group => $tabs){
            foreach($tabs as $tab){
                foreach(array_keys($data) as $key){
                    if(isset($tab['tabLabel']) && $tab['tabLabel'] == $key){
                        if($group == 'checkboxTabs') {
                            $tab['selected'] = $data[$key];
                        }elseif($group == 'listTabs'){
                            $tab['value'] = $data[$key];
                            foreach($tab['listItems'] as $k => $option){
                                $tab['listItems'][$k]['selected'] = $option['value'] == $data[$key] ? 'true' : 'false';
                            }
                        }else{
                            $tab['value'] = $data[$key];
                        }
                    }elseif(isset($tab['groupName']) && $tab['groupName'] == $key){
                        foreach($tab['radios'] as $radio){
                            if($radio['value'] == $data[$key]){
                                $radio['selected'] = 'true';
                            }else{
                                $radio['selected'] = 'false';
                            }
                            $updatedRadios[] = $radio;
                        }
                        $tab['radios'] = $updatedRadios;
                    }
                }
                $updatedTabs[$recipientId][$group][] = $tab;
            }
        }
        return $updatedTabs;
    }

    public function customFields()
    {
        return Docusign::getEnvelopeCustomFields($this->getEnvelopeId());
    }

    public function templates()
    {
        return Docusign::getEnvelopeTemplates($this->getEnvelopeId());
    }

    public function documents($documentId = null, $download = null)
    {
        if($documents = $this->{config('docusign.documents_field')}){
            return $documents;
        }elseif($documentId){
            return Docusign::getEnvelopeDocument($this->getEnvelopeId(), $documentId, $download);
        }
        return Docusign::getEnvelopeDocuments($this->getEnvelopeId());
    }

    public function view($recipientId, $returnUrl)
    {
        $signer = Arr::first($this->recipients()['signers'], function ($key, $signer) use($recipientId) {
            return $signer['recipientId'] == $recipientId;
        });
        $recipient = Arr::only($signer, ['recipientId', 'clientUserId', 'userName', 'email']);
        return \Docusign::createRecipientView($this->getEnvelopeId(), array_merge($recipient, [
            'returnUrl' => $returnUrl,
            'userName' => $signer['name'],
            'authenticationMethod' => 'email',
            'AuthenticationInstant' => Carbon::now()->toDateTimeString()
        ]));
    }

    public function resend()
    {
        $signers = Arr::where($this->recipients()['signers'], function ($key, $signer) {
            return $signer['status'] == 'sent';
        });
        return Docusign::modifyRecipients($this->getEnvelopeId(), compact('signers'), true);
    }

    public function image($documentId, $pageId, $dpi = null, $maxWidth = null, $maxHeight = null)
    {
        return Docusign::getPageImage($this->getEnvelopeId(), $documentId, $pageId, $dpi, $maxWidth, $maxHeight);
    }

    public static function boot() {
        parent::boot();
        static::creating(function($document) {
            $envelope = [
                'status' => $document->status,
                'templateId' => $document->templateId,
                'emailSubject' => $document->emailSubject,
                'templateRoles' => $document->templateRoles,
            ];
            $envelope = Docusign::createEnvelope($envelope);
            $document->{config('docusign.envelope_field')} = $envelope['envelopeId'];
            if($recipients = config('docusign.save_recipient_tabs')){
                $tabs = [];
                foreach($recipients as $recipientId){
                    $tabs[$recipientId] = $document->tabs($recipientId);
                }
                $document->{config('docusign.tabs_field')} = $tabs;
            }
            if($documentsField = config('docusign.documents_field')){
                $document->$documentsField = $document->documents();
            }

        });
        static::updating(function($document) {
            if($document->isDirty(config('docusign.tabs_field'))){
                foreach($document->{config('docusign.tabs_field')} as $recipientId => $tabs){
                    Docusign::updateRecipientTabs($document->getEnvelopeId(), $recipientId, $tabs);
                }
            }
            switch($document->status){
                case 'voided':
                    return Docusign::updateEnvelope($document->getEnvelopeId(), [
                        'status' => $document->status,
                        'voidedReason' => $document->{config('docusign.tabs_field')}
                    ]);
                    break;
                case 'sent':
                    return Docusign::updateEnvelope($document->getEnvelopeId(), [
                        'status' => 'sent'
                    ]);
                    break;
            }
        });
        static::deleting(function($document) {
            return Docusign::moveEnvelopes([$document->getEnvelopeId()], 'recyclebin', 'inbox');
        });
    }
}

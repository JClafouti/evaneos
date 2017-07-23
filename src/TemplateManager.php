<?php

class TemplateManager
{
    const VAR_PATTERN = '[%var%]';
    
    /**
    *
    */
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    /**
    *
    */
    private function replaceTemplateVar($text, $var, $value)
    {
        $var = str_replace('%var%', $var, self::VAR_PATTERN);
        
        if(strpos($text, $var) !== false) {
            $text = str_replace($var, $value, $text);
        }

        return $text;
    }

    /**
    *
    */
    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;

        if ($quote)
        {
            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

            if(strpos($text, '[quote:destination_link]') !== false){
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            }
            
            // replace summary_html
            $text = $this->replaceTemplateVar($text, 'quote:summary_html', Quote::renderHtml($_quoteFromRepository));

            // replace summary
            $text = $this->replaceTemplateVar($text, 'quote:summary', Quote::renderText($_quoteFromRepository));
            
            // replace detination name
            $text = $this->replaceTemplateVar($text, 'quote:destination_name', $destinationOfQuote->countryName);
            
        }

        // replace destination link
        $text = $this->replaceTemplateVar($text, 'quote:destination_link', '');

        if (isset($destination))
            $text = $this->replaceTemplateVar($text, 'quote:destination_link', $usefulObject->url . '/' . $destination->countryName . '/quote/' . $_quoteFromRepository->id);        
                   

        /*
         * replace user first name
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            $text = $this->replaceTemplateVar($text, 'user:first_name', ucfirst(mb_strtolower($_user->firstname)));
        }

        return $text;
    }
}

<?php

class TemplateManager
{
    // generic var pattern
    const VAR_PATTERN = '[%var%]';
    
    /**
    * Give a template as an argument and returns the template with vars filled with data
    * @param Template tpl // Template object 
    * @param array Data // Array containing template data
    * @return Template object
    */
    public function getTemplateComputed(Template $tpl, array $data)
    {        
        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    /**
    * Replace a template var with the value given as argument
    * @param string text // text to replace vars in
    * @param string var // var name to replace
    * @param string value // replace var with this
    * @return string // returns the text with replacements
    */
    private function replaceTemplateVar($text, $var, $value)
    {
        // construct var pattern to be found in template
        $var = str_replace('%var%', $var, self::VAR_PATTERN);
        
        // if var pattern is found in template text replace it with value given as argument
        if(strpos($text, $var) !== false) {
            $text = str_replace($var, $value, $text);
        }

        // returns template text
        return $text;
    }

    /**
    * 
    * @param text // template text
    * @param array data // array containing data objects like Quote and User used for replacements
    * @return  string text // return text with all vars replaced 
    */
    private function computeText($text, array $data)
    {
        // get application context, used to retrieve current user infos for current site
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        // Retrieve Quote and User from data array
        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        $user = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();

        // Replace quotes vars if Quote Object exists
        if ($quote)
        {
            $quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $siteFromRepository  = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);
            
            // replace summary_html
            $text = $this->replaceTemplateVar($text, 'quote:summary_html', Quote::renderHtml($quoteFromRepository));

            // replace summary
            $text = $this->replaceTemplateVar($text, 'quote:summary', Quote::renderText($quoteFromRepository));
            
            // replace detination name
            $text = $this->replaceTemplateVar($text, 'quote:destination_name', $destinationOfQuote->countryName);

            // replace destination link
            $text = $this->replaceTemplateVar($text, 'quote:destination_link', 
                $siteFromRepository->url . 
                '/' . 
                DestinationRepository::getInstance()->getById($quote->destinationId)->countryName . 
                '/quote/' . 
                $quoteFromRepository->id
                );
            
        }

        // replace user first name if User Object exists        
        if($user) {
            $text = $this->replaceTemplateVar($text, 'user:first_name', ucfirst(mb_strtolower($user->firstname)));
        }

        // return computed text
        return $text;
    }
}

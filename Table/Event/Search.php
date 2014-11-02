<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Table\Event;

/**
 *
 *
 * @package Table\Event
 */
class Search extends \Form\Event\Button
{
    /*
     * @var \Table\Table
     */
    protected $table = null;

    /**
     *
     *
     * @param string $name
     * @param \Table\Table $table
     */
    public function __construct($name, \Table\Table $table)
    {
        parent::__construct($name);
        $this->table = $table;
    }


    /**
     * executed
     *
     * @param \Form\Form $form
     */
    public function update($form)
    {
        if ($this->getForm()->hasErrors()) {
            return;
        }

        $sesId = $this->table->getSessionHash();
        $values = $form->getFormValuesArray();

        $this->getSession()->set($sesId, $values);

        $url = $this->getUri();
        if ($form->isSubmitted()) {
            //reset offset for a search query.
            $url->delete($this->table->getObjectKey('offset'));
        }
        $this->setRedirectUrl($url);

    }

    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<XML
<?xml version="1.0"?>
<button type="submit" class="btn btn-primary btn-xs tip-bottom" var="element"><i var="icon" class="icon icon-search"></i> <span var="text">Submit</span></button>
XML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }
}
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
class Clear extends \Form\Event\Link
{
    /*
     * @var \Table\Table
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $deleteParams = array();


    /**
     * __construct
     *
     * @param \Table\Table $table
     * @param array $deleteParams
     */
    public function __construct(\Table\Table $table, $deleteParams = array('search-reset', 'keywords'))
    {
        parent::__construct('clear');
        $this->setLabel('Clear All');
        $this->deleteParams = $deleteParams;
        $this->table = $table;
    }

    /**
     * (non-PHPdoc)
     * @param \Tk\Observable $form
     */
    public function update($form)
    {
        $sesId = $this->table->getSessionHash();
        $this->getSession()->delete($sesId);

        $url = $this->getUri();
        $url->delete('search-reset');
        if ($form->isSubmitted()) {
            //reset offset for a search query.
            $url->delete($this->table->getObjectKey('offset'));
        }
        $url->redirect();
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
<a href="javascript:;" var="element" class="btn btn-default btn-xs tk-btnLink"><i var="icon" class="icon icon-remove"></i> <span var="text">Submit</span></a>
XML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

}
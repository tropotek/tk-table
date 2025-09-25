<?php
namespace Tk\Table;

use Tk\Table;

abstract class TableRenderer extends Renderer
{

    const string CSS_SELECTED = 'active';
    const string CSS_DISABLED = 'disabled';

    const array LIMIT_LIST    = [
        '-- All --' => 0,
        '10'  => 10,
        '25'  => 25,
        '50'  => 50,
        '100' => 100,
        '250' => 250,
    ];

    protected string  $templatePath  = '';
    /** @var array<string,Renderer>  */
    protected array   $footer        = [];
    protected int     $maxPages      = 10;
    protected bool    $footerEnabled = true;


    public function __construct(Table $table, string $templatePath = '')
    {
        if (!is_file($templatePath)) {
            throw new Exception("File not found: $templatePath");
        }
        $this->setTable($table);
        $this->templatePath = $templatePath;
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    public function getMaxPages(): int
    {
        return $this->maxPages;
    }

    public function setMaxPages(int $maxPages): void
    {
        $this->maxPages = $maxPages;
    }

    public function isFooterEnabled(): bool
    {
        return $this->footerEnabled;
    }

    public function setFooterEnabled(bool $footerEnabled): static
    {
        $this->footerEnabled = $footerEnabled;
        return $this;
    }

    public function addFooter(string $name, Renderer $renderer): static
    {
        $this->footer[$name] = $renderer;
        return $this;
    }

    /**
     * @return array<string,Renderer>
     */
    public function getFooter(): array
    {
        return $this->footer;
    }

}
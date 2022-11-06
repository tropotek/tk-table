<?php
namespace Tk\Table\Db;


use Tk\Table;

class Tool extends \Tk\Db\Tool
{


    public function __construct(Table $table)
    {
        $tool = Tool::create($defaultOrderBy, $defaultLimit);
        $tool->setInstanceId($this->getTableId());

        if ($this->orderBy === null && $defaultOrderBy) $this->setOrderBy($defaultOrderBy);
        if ($this->limit === null && $defaultLimit) $this->setLimit($defaultLimit);

        $tool->updateFromArray($this->all($tool->makeInstanceKey('')));

        $request = Factory::instance()->getRequest();
        $updated = $tool->updateFromArray($request->query->all());

        // Redirect on update
        if ($updated) {
            $this->replace($tool->toArray());
            Uri::create()
                ->remove($this->makeInstanceKey(Tool::PARAM_ORDER_BY))
                ->remove($this->makeInstanceKey(Tool::PARAM_LIMIT))
                ->remove($this->makeInstanceKey(Tool::PARAM_OFFSET))
                ->redirect();
        }

    }


}
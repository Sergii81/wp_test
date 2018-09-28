<?php

namespace codingninjas;

use \Exception;

class TableTasks
{
    /**
     * thead
     * @return string
     */
    public function thead()
    {
        $cols = [
            __('ID', 'cn'),
            __('Title', 'cn'),
            __('Freelancer', 'cn'), //On the page “tasks” add a column “Freelancer“.
            __('Date', 'cn')
        ];

        $cols = apply_filters ('cn_tasks_thead_cols', $cols);

        $content = App::view (
            ['table', 'thead.php'],
            ['cols' => $cols]
        );

        return $content;
    }

    /**
     * tbody
     * @return string
     */
    public function tbody()
    {
        $tasks = (new ModelTasks())->getAll();
        $rows = [];

        if ($tasks) {
            foreach ($tasks as $task) {
                $cols = [
                    $task->id(),
                    $task->title(),
                    $task->cfreelancer(),
                    $task->cdate()
                ];

                $cols = apply_filters('cn_tasks_tbody_row_cols', $cols, $task);

                $rows[] = $cols;
            }
        }

        $rows = apply_filters ('cn_tasks_tbody_rows', $rows);

        $content = App::view (
            ['table', 'tbody.php'],
            ['rows' => $rows]
        );

        return $content;
    }

    /**
     * tfoot
     * @return string
     */
    public function tfoot()
    {
        $cols = [];

        $cols = apply_filters ('cn_tasks_tfoot_cols', $cols);

        $content = App::view (
            ['table', 'tfoot.php'],
            ['cols' => $cols]
        );

        return $content;
    }

    /**
     * render table
     * @return string
     */
    public function render()
    {
        $content = '';
        $content .= $this->thead();
        $content .= $this->tbody();
        $content .= $this->tfoot();

        $attributes = [
            'width' => '100%',
            'class' => 'table table-striped table-bordered table-hover',
            'id' => 'tasks_table'
        ];

        return App::view (
            ['table', 'table.php'],
            [
                'attributes' => App::join($attributes),
                'content' => $content
            ]
        );
    }
}
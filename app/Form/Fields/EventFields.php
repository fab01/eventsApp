<?php

namespace App\Form\Fields;

use App\Form\Form as Form;
use FormManager\Builder as F;
use App\Models\Event as Event;

class EventFields extends Form
{
    /**
     * @return array
     */
    public function createSet()
    {
        // CREATE - Title
        $title = F::text()->attr('name', 'title')
          ->val($this->getMessage('title'))
          ->addClass('form-control')
          ->label('Title of the event');

        // CREATE - Status
        $status = F::select()->attr('name', 'status')
          ->options(
            [
              '0' => 'Not Active',
              '1' => 'Active'
            ])
          ->val($this->getMessage('status')) //Always after options definitions.
          ->addClass('form-control')
          ->label('Status of the event');

        $fields = [
          'title' => $title,
          'status' => $status,
        ];

        return $fields;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function updateSet($id)
    {
        $data = Event::find($id);
        // UPDATE - Title
        $title = F::text()->attr('name', 'title')
          ->val($data->title)
          ->addClass('form-control')
          ->label('Title of the event');

        // UPDATE - Status
        $status = F::select()->attr(['name' => 'status'])
          ->options(
            [
              '0' => 'Not Active',
              '1' => 'Active'
            ])
          ->val($data->status) // Always after options definitions.
          ->addClass('form-control')
          ->label('Status of the event');

        // UPDATE - Hidden ID
        $eventId = F::Hidden()->attr(['name' => 'id'])->val($id);

        $fields = [
          'title' => $title,
          'status' => $status,
          'id' => $eventId,
        ];

        return $fields;
    }

    /**
     * @param null $id
     *
     * @return array
     */
    public function selectAll( $id = null )
    {
        $data = Event::All()->where('deleted', 0);
        $options = array();
        foreach ($data as $event) {
            $options[$event->id] = $event->title;
        }

        $list = F::select()->attr('name', 'events')
          ->options($options)
          ->addClass('form-control');

        if (null !== $id)
            $list->val($id);

        $fields = ['events' => $list];

        return $fields;
    }

    /**
     * @param null $id
     *
     * @return mixed
     */
    public function embeddedList($id = null)
    {
        $list = $this->selectAll($id);
        $events = $list['events']->label('Select an event from the list');

        return $events;
    }
}
<?php

namespace App\Controllers;

use App\Models\Event;
use App\Models\EventSubscription;
use Respect\Validation\Validator as v;
use App\Auth\Auth as Auth;
use Slim\Http\Stream;

class EventController extends Controller
{
    //==== READ

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Select All events
     */
    public function getAll($request, $response)
    {
        $event = new Event();
        return $this->view->render($response, 'controller/event/all.html.twig', ['events' => $event->allWithCountMeetUp()]);
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Select All events
     */
    public function getEventDetails($request, $response)
    {
        $subscriptions = new EventSubscription();
        $subscribers = $subscriptions->getAll();
        return $this->view->render($response, 'controller/event/details.html.twig',
          [
            'subscribers' => $subscribers,
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Download Abstract.
     */
    public function getAbstractDownload($request, $response, $args)
    {
        $subscription = EventSubscription::find($args['id']);
        $file = $this->container->get('upload_directory') . $subscription->event_id . '/' . $subscription->abstract;

        $fh = fopen($file, 'rb');
        $stream = new Stream($fh); // create a stream instance for the response body

        return $response->withHeader('Content-Type', 'application/force-download')
          ->withHeader('Content-Type', 'application/octet-stream')
          ->withHeader('Content-Type', 'application/download')
          ->withHeader('Content-Description', 'File Transfer')
          ->withHeader('Content-Transfer-Encoding', 'binary')
          ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
          ->withHeader('Expires', '0')
          ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
          ->withHeader('Pragma', 'public')
          ->withBody($stream); // all stream contents will be sent to the response
    }

    //==== CREATE

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Create a new event. Get Form.
     */
    public function getEventCreate($request, $response)
    {
        $form = $this->form->getFields('Event')->createSet();

        return $this->view->render($response, 'controller/event/manage.html.twig',
          [
            'form_title'  => 'Create new event',
            'form_submit' => 'Create event',
            'form_action' => 'event.create',
            'form' => $form,
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Create a new event. On form submit.
     */
    public function postEventCreate($request, $response)
    {
        $validation = $this->validator->validate($request,
          [
            'title' => v::notEmpty(),
            'status' => v::in(['0', '1']),
            'start_date' => v::notEmpty()->date('d-m-Y'),
            'end_date' => v::notEmpty()->date('d-m-Y'),
          ]
        );

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('event.create'));
        }

        $event = Event::create([
          'title'  => $request->getParam('title'),
          'start_date' => date_format(date_create($request->getParam('start_date')), 'Y-m-d H:i:s'),
          'end_date' => date_format(date_create($request->getParam('end_date')), 'Y-m-d H:i:s'),
          'status' => $request->getParam('status'),
        ]);

        if (!file_exists($this->container->get('upload_directory').$event->id)) {
            @mkdir($this->container->get('upload_directory').$event->id);
            @chmod($this->container->get('upload_directory').$event->id, 0777);
        }

        $this->flash->addMessage('success', "Event {$event->title} has been correctly created!");
        return $response->withRedirect($this->router->pathFor('event.all'));
    }

    //==== UPDATE

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update event. Get Form.
     */
    public function getEventUpdate($request, $response, $args)
    {
        $form = $this->form->getFields('Event')->updateSet($args['id']);

        // Field Status edit is reserved to Admin.
        if (!Auth::isAdmin()) {
            $form['status']->attr(['disabled' => 'disabled']);
        }

        return $this->view->render($response, 'controller/event/manage.html.twig',
          [
            'form_title'  => 'Update event',
            'form_submit' => 'Save',
            'form_action' => 'event.update',
            'form' => $form,
            'id' => $args['id'],
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update event. On form submit.
     */
    public function postEventUpdate($request, $response)
    {
        $params = [
          'id' => v::notEmpty(),
          'title' => v::notEmpty(),
          'start_date' => v::notEmpty()->date('d-m-Y'),
          'end_date' => v::notEmpty()->date('d-m-Y'),
        ];
        $toUpdate = [
          'title'  => $request->getParam('title'),
          'start_date' => date_format(date_create($request->getParam('start_date')), 'Y-m-d H:i:s'),
          'end_date' => date_format(date_create($request->getParam('end_date')), 'Y-m-d H:i:s'),
        ];

        if (Auth::isAdmin()) {
            $params['status'] = v::in(['0', '1']);
            $toUpdate['status'] = $request->getParam('status');
        }

        $validation = $this->validator->validate($request, $params);
        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('event.update', ['id' => $request->getParam('id')]));
        }

        // Only one Event can be active.
        if ($request->getParam('status') == 1) {
            Event::where('id', '<>', $request->getParam('id'))
              ->update(['status' => 0]);
        }

        Event::where('id', $request->getParam('id'))->update($toUpdate);
        if (!file_exists($this->container->get('upload_directory').$request->getParam('id'))) {
            @mkdir($this->container->get('upload_directory').$request->getParam('id'));
            @chmod($this->container->get('upload_directory').$request->getParam('id'), 0777);
        }
        $this->flash->addMessage('success', "Event has been correctly updated!");

        return $response->withRedirect($this->router->pathFor('event.all'));
    }

    //==== DELETE

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update event. Get Form.
     */
    public function getEventDelete($request, $response, $args)
    {
        $event = new Event();
        if (!Auth::isAdmin()) {
            $this->flash->addMessage('error', "You don't have permission to delete an event!");
            return $response->withRedirect($this->router->pathFor('event.all'));
        }
        if ($event->isActive($args['id'])) {
            $this->flash->addMessage('error', "You can't delete an event when is Active!");
            return $response->withRedirect($this->router->pathFor('event.all'));
        }
        else {
            Event::where('id', $args['id'])->update(['deleted' => '1']);
            $this->flash->addMessage('success', "Event deleted!");
            return $response->withRedirect($this->router->pathFor('event.all'));
        }
    }

    //==== UPDATE EVENT DETAILS

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update event details. Get Form.
     */
    public function getEventDetailsUpdate($request, $response, $args)
    {
        $form = $this->form->getFields('EventSubscription')->adminUpdateSet($args['id']);

        return $this->view->render($response, 'controller/event/update.html.twig',
          [
            'form_title'  => 'Update event subscription',
            'form_submit' => 'Save',
            'form_action' => 'event.details.update',
            'form' => $form,
            'id' => $args['id'],
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update event details. On form submit.
     */
    public function postEventDetailsUpdate($request, $response)
    {
        $not_full_board = [4, 5, 6, 7, 8];

        $params = [
          'id' => v::notEmpty(),
          'accommodations' => v::notEmpty(),
        ];
        $toUpdate = [
          'one_night' => '0000-00-00',
          'accommodation_id' => $request->getParam('accommodations'),
        ];

        if (in_array($request->getParam('accommodations'), $not_full_board)) {
            $params = [
                'one_night' => v::notEmpty()->date('d-m-Y'),
            ];
            $toUpdate = [
                'one_night' => date_format(date_create($request->getParam('one_night')), 'Y-m-d H:i:s'),
            ];
        }

        $validation = $this->validator->validate($request, $params);
        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('event.details.update', ['id' => $request->getParam('id')]));
        }

        EventSubscription::where('id', $request->getParam('id'))->update($toUpdate);
        $this->flash->addMessage('success', "Event details have been correctly updated!");

        return $response->withRedirect($this->router->pathFor('event.details'));
    }
}

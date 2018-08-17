<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Models\Event;
use App\Models\MeetUp;
use App\Models\MeetUpSubscription;
use FormManager\Fields\Datetime;
use Respect\Validation\Validator as v;

class MeetUpController extends Controller
{
    //==== READ
    public function getAll($request, $response)
    {
        $events = $this->form->getFields('Event')->selectAll();
        return $this->view->render($response, 'controller/meetup/all.html.twig', ['events' => $events]);
    }

    public function getAllByEid($request, $response, $args)
    {
        $meetup = new MeetUp();
        $meetUps = $meetup->allWithCountSubscribers($args['eid']);
        $events = $this->form->getFields('Event')->selectAll($args['eid']);

        return $this->view->render($response, 'controller/meetup/all.html.twig',
          [
            'events'    => $events,
            'meetUps'   => $meetUps,
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     * @param $args
     *
     * @return mixed
     */
    public function getMeetUpDetails($request, $response, $args)
    {
        $meetUpSubscriptions = new MeetUpSubscription();
        $subscribers = $meetUpSubscriptions->allParticipantsByMeetupId($args['id']);
        $meetup = MeetUp::find($args['id']);

        return $this->view->render($response, 'controller/meetup/details.html.twig',
          [
            'subscribers'   => $subscribers,
            'eventId'       => $meetup->event_id,
            'id'            => $args['id'],
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     */
    public function postAll($request, $response)
    {
        $meetup = new MeetUp();
        $meetUps = $meetup->allWithCountSubscribers($request->getParam('events'));
        $events = $this->form->getFields('Event')->selectAll($request->getParam('events'));

        return $this->view->render($response, 'controller/meetup/all.html.twig',
          [
            'events'    => $events,
            'meetUps'   => $meetUps,
          ]
        );
    }

    //==== CREATE
    public function getMeetUpCreate($request, $response)
    {
        $form = $this->form->getFields('MeetUp')->createSet();

        return $this->view->render($response, 'controller/meetup/manage.html.twig',
          [
            'form_title'    => 'Crea nuova Tavola Rotonda',
            'form_submit'   => 'Crea Tavola Rotonda',
            'form_action'   => 'meetup.create',
            'form'          => $form,
          ]
        );
    }

    public function postMeetUpCreate($request, $response)
    {
        $validation = $this->validator->validate($request,
          [
            'title'     => v::notEmpty(),
            'date'      => v::notEmpty()->date('d-m-Y'),
            'events'    => v::notEmpty(),
            'places'    => v::notEmpty()->min(1, true),
          ]
        );

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('meetup.create'));
        }

        $meetup = MeetUp::create([
          'title'               => $request->getParam('title'),
          'date'                => date_format(date_create($request->getParam('date')), 'Y-m-d H:i:s'),
          'description'         => $request->getParam('description'),
          'event_id'            => $request->getParam('events'),
          'available_places'    => $request->getParam('places'),
        ]);

        $this->flash->addMessage('success', "Meetup {$meetup->title} has been correctly created!");

        return $response->withRedirect($this->router->pathFor('meetup.all'));
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
    public function getMeetUpUpdate($request, $response, $args)
    {
        $form = $this->form->getFields('MeetUp')->updateSet($args['id']);
        $meetup = MeetUp::find($args['id']);

        return $this->view->render($response, 'controller/meetup/manage.html.twig',
          [
            'form_title'    => 'Update MeetUp',
            'form_submit'   => 'Save',
            'form_action'   => 'meetup.update',
            'form'          => $form,
            'eventId'       => $meetup->event_id,
            'id'            => $args['id'],
          ]
        );
    }

    /**
     * @param $request
     * @param $response
     *
     * @return mixed
     *
     * Update meet up. On form submit.
     */
    public function postMeetUpUpdate($request, $response)
    {
        $params = [
          'id'      => v::notEmpty(),
          'title'   => v::notEmpty(),
          'date'    => v::notEmpty()->date('d-m-Y'),
          'events'  => v::notEmpty(),
          'places'  => v::notEmpty()->min(1, true),
        ];

        $toUpdate = [
          'title'               => $request->getParam('title'),
          'date'                => date_format(date_create($request->getParam('date')), 'Y-m-d H:i:s'),
          'description'         => $request->getParam('description'),
          'event_id'            => $request->getParam('events'),
          'available_places'    => $request->getParam('places'),
        ];

        $validation = $this->validator->validate($request, $params);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('meetup.update', ['id' => $request->getParam('id'), 'eventId' =>  $request->getParam('events')]));
        }

        MeetUp::where('id', $request->getParam('id'))->update($toUpdate);
        $this->flash->addMessage('success', "Meetup has been correctly updated!");

        return $response->withRedirect($this->router->pathFor('meetup.update', ['id' => $request->getParam('id'), 'eventId' =>  $request->getParam('events')]));
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
    public function getMeetUpDelete($request, $response, $args)
    {
        if (!Auth::isAdmin()) {
            $this->flash->addMessage('error', "You don't have permission to delete a Meetup!");
            return $response->withRedirect($this->router->pathFor('meetup.all'));
        }
        else {
            MeetUp::where('id', $args['id'])->update(['deleted' => '1']);
            $this->flash->addMessage('success', "Meetup deleted!");
            return $response->withRedirect($this->router->pathFor('meetup.all.eid', ['eid' => 1]));
        }
    }
}

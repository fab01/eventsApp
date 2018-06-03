<?php
/**
 * Created by PhpStorm.
 * User: fabrizio
 * Date: 06/05/18
 * Time: 22:34
 */

namespace App\Controllers;

use App\Models\Event;
use App\Models\EventSubscription;
use App\Models\Subscriber;
use Respect\Validation\Exceptions\FalseValException;
use Respect\Validation\Validator as v;
use Slim\Http\UploadedFile;

class SubscriptionController extends Controller
{
    public function getEventSubscriptionCreate($request, $response, $args)
    {
        $event = Event::where('status', 1)->first();
        $status = (is_object($event)) ? $event->status : NULL;

        if (NULL !== $status && $status == 1) {
            $form = $this->form->getFields('EventSubscription')->createSet();

            return $this->view->render($response, 'controller/subscription/event.html.twig',
              [
                'form_title'  => 'Event subscription',
                'form_submit' => 'Subscribe me!',
                'form_action' => 'event.subscription.create',
                'form' => $form,
              ]
            );
        }
        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function postEventSubscriptionCreate($request, $response)
    {
        $directory     =  $this->container->get('upload_directory') . $_SESSION['eid'];
        $uploadedFiles =  $request->getUploadedFiles();
        $uploadedFile  =  $uploadedFiles['abstract_file'];
        $filename      =  $uploadedFile->getClientFilename();
        $application   =  $request->getParam('abstract_apply');
        $abstract      =  true;

        if (null != $filename || $application) {

            // File extensions.
            $file_validation = v::oneOf(
              v::extension('pdf'),
              v::extension('doc'),
              v::extension('docx')
            )->validate($filename);

            // File apply for.
            $apply_validation = v::notEmpty()->validate($application);

            // Validate extensions.
            if (!$file_validation) {
                $this->validator->setCustomErrors(
                  'abstract_file',
                  'Please check the abstract file. Make sue the extension is on of the following: pdf, doc, docx'
                );
                $abstract = false;
            }

            // Validate application.
            if (!$apply_validation) {
                $this->validator->setCustomErrors(
                  'abstract_apply',
                  'Please select the application of the file.'
                );
                $abstract = false;
            }
        }

        // Validate accommodation.
        $validation = $this->validator->validate($request, ['accommodations' => v::notEmpty()]);
        if ($validation->failed() || !$abstract) {
            $this->flash->addMessage('error', "Something went wrong with the submission. Check for the errors reported in the form.");
            return $response->withRedirect($this->router->pathFor('event.subscription.create'));
        }

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            $subscription = EventSubscription::create([
              'event_id'         => $_SESSION['eid'],
              'subscriber_id'    => $_SESSION['uid'],
              'accommodation_id' => $request->getParam('accommodations'),
              'abstract'         => $filename,
              'apply'            => $application,
            ]);
            $this->flash->addMessage('success', 'uploaded ' . $filename);
            return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $subscription->id]));
        }
        return $response->withRedirect($this->router->pathFor('event.subscription.create'));
    }

    public function getEventSubscriptionUpdate($request, $response, $args)
    {
        $subscription = new EventSubscription();
        $event = Event::where('status', 1)->first();
        $status = (is_object($event)) ? $event->status : NULL;

        if ($subscription->isAuthorized($args['id'])) {
            $form = $this->form->getFields('EventSubscription')->updateSet($args['id']);

            return $this->view->render($response, 'controller/subscription/event.html.twig',
              [
                'form_title'  => 'Event subscription Update',
                'form_submit' => 'Update subscription',
                'form_action' => 'event.subscription.update',
                'form' => $form,
                'id' => $args['id'],
              ]
            );
        }
        return $this->view->render($response, 'http/403.html.twig');
    }

    public function postEventSubscriptionUpdate($request, $response)
    {
        $directory     =  $this->container->get('upload_directory') . $_SESSION['eid'];
        $uploadedFiles =  $request->getUploadedFiles();
        $uploadedFile  =  $uploadedFiles['abstract_file'];
        $filename      =  $uploadedFile->getClientFilename();
        $application   =  $request->getParam('abstract_apply');
        $abstract      =  true;

        if (null != $filename || $application) {

            // File extensions.
            $file_validation = v::oneOf(
              v::extension('pdf'),
              v::extension('doc'),
              v::extension('docx')
            )->validate($filename);

            // File apply for.
            $apply_validation = v::notEmpty()->validate($application);

            // Validate extensions.
            if (!$file_validation) {
                $this->validator->setCustomErrors(
                  'abstract_file',
                  'Please check the abstract file. Make sue the extension is on of the following: pdf, doc, docx'
                );
                $abstract = false;
            }

            // Validate application.
            if (!$apply_validation) {
                $this->validator->setCustomErrors(
                  'abstract_apply',
                  'Please select the application of the file.'
                );
                $abstract = false;
            }
        }

        if (!$abstract) {
            $this->flash->addMessage('error', "Something went wrong with the submission. Check for the errors reported in the form.");
        }

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            $this->flash->addMessage('success', 'uploaded ' . $filename);
        }

        return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $request->getParam('id')]));

        // ELSE SUBSCRIBE USER ...
        //return $response->withRedirect($this->router->pathFor('event.subscription.create'));
    }

    /* @todo: MeetupSubscription create. */
    public function getMeetupSubscriptionCreate($request, $response, $args) {}

    public function postMeetupSubscriptionCreate($request, $response) {}

    /**
     * Moves the uploaded file to the upload directory.
     *
     * @param string $directory directory to which the file is moved
     * @param UploadedFile $uploaded file uploaded file to move
     * @return string filename of moved file
     */
    public function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $filename  = time() . '_' . $uploadedFile->getClientFilename();
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }
}
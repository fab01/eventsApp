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
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Respect\Validation\Exceptions\FalseValException;
use Respect\Validation\Validator as v;
use Slim\Http\UploadedFile;

class SubscriptionController extends Controller
{

    /**
     * @param $request
     * @param $response
     * @param $args
     *
     * @return mixed
     * Create the Event.
     */
    public function getEventSubscriptionCreate($request, $response, $args)
    {
        $event = Event::where('status', 1)->first();
        $status = (is_object($event)) ? $event->status : NULL;

        if (NULL !== $status && $status == 1) {
            $form = $this->form->getFields('EventSubscription')->createSet();

            return $this->view->render($response, 'controller/subscription/event.html.twig',
              [
                'form_title'  => 'Event registration',
                'form_submit' => 'Register me!',
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

        // If user is updating file.
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

        // Upload file ONLY IF user is currently uploading a file.
        if (null != $filename || $application) {
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
            } else {
                $this->flash->addMessage('error', "Something went wrong with file upload.");
                return $response->withRedirect($this->router->pathFor('event.subscription.create'));
            }
        }

        // Save data into DB.
        $subscription = EventSubscription::create([
          'accommodation_id' => $request->getParam('accommodations'),
          'event_id'         => $_SESSION['eid'],
          'subscriber_id'    => $_SESSION['uid'],
          'abstract'         => $filename,
          'apply'            => $application,
        ]);

        // If DB Save successful, notify via mail and redirect.
        if (NULL !== $subscription->id) {
            $this->notifyUser();
            return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $subscription->id]));
        }

        return $response->withRedirect($this->router->pathFor('event.subscription.create'));
    }

    /**
     * @param $request
     * @param $response
     * @param $args
     *
     * @return mixed
     * Update the Event.
     */
    public function getEventSubscriptionUpdate($request, $response, $args)
    {
        $subscription = new EventSubscription();
        $event = Event::where('status', 1)->first();
        $status = (is_object($event)) ? $event->status : NULL;

        if ($subscription->isAuthorized($args['id'])) {
            $form = $this->form->getFields('EventSubscription')->updateSet($args['id']);

            return $this->view->render($response, 'controller/subscription/event.html.twig',
              [
                'form_title'  => 'Event registration Update',
                'form_submit' => 'Update registration',
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
        $uploadedFiles =  $request->getUploadedFiles();
        $application   =  $request->getParam('abstract_apply');
        $directory     =  $this->container->get('upload_directory') . $_SESSION['eid'];
        $uploadedFile  =  $uploadedFiles['abstract_file'];
        $filename      =  $uploadedFile->getClientFilename();
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

            // remove old file if exists before replace with new one.
            $current_file = EventSubscription::find($request->getParam('id'));
            @unlink($directory . DIRECTORY_SEPARATOR . $current_file->abstract);

            // store data in table.
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            $toUpdate = [
              'abstract' => $filename,
              'apply'    => $request->getParam('abstract_apply'),
            ];

            EventSubscription::where('id', $request->getParam('id'))->update($toUpdate);
            $this->flash->addMessage('success', 'uploaded ' . $filename);
        }

        return $response->withRedirect($this->router->pathFor('event.subscription.update', ['id' => $request->getParam('id')]));
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
        $filename  = time() . '_' . str_replace(" ", "-", $uploadedFile->getClientFilename());
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    public function notifyUser()
    {
        // Subscriber variables.
        $client_mail = $_SESSION['user']->mail;
        $client_name = $_SESSION['user']->field_first_name["und"][0]['value'];

        // @ administrator
        $sbjAdmin = "New subscriber to IIM event";
        $msgAdmin = $client_name . " will participate to the next IIM event";

        // @ cliente
        $sbjClient = "Thank you, " . $client_name;
        $msgClient = "Subscription to the next IIM Event confirmed!";

        $mail = new PHPMailer();

        try {
            //Server settings
            /*$mail->SMTPDebug = 0;                            // Enable verbose debug output
            $mail->isSMTP();                                 // Set mailer to use SMTP
            $mail->Host = 'smtp.aruba.it';                   // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                          // Enable SMTP authentication
            $mail->Username = 'noreply-iim@coram-iim.it';    // SMTP username
            $mail->Password = 'iim2018!';                    // SMTP password
            $mail->SMTPSecure = 'ssl';                       // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                               // TCP port to connect to*/

            // Mail to Admin.
            $mail->setFrom($client_mail);
            $mail->addAddress('fabriziosabato@gmail.com');
            $mail->Subject = $sbjAdmin;
            $mail->Body    = $msgAdmin;
            $mail->send();
            $mail->clearAddresses();

            // Mail to Client (Confirmation).
            $mail->setFrom('noreply@coram-iim.it');
            $mail->addAddress($client_mail);
            $mail->Subject = $sbjClient;
            $mail->Body    = $msgClient;
            $mail->send();

            $this->flash->addMessage('success', 'Registration submitted!');
        }
        catch (Exception $e)
        {
            $this->flash->addMessage('error', 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo);
        }
    }
}

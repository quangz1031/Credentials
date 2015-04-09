<?php

/*
 * This file is part of Laravel Credentials.
 *
 * (c) Graham Campbell <graham@mineuk.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Credentials\Http\Controllers;

use Cartalyst\Sentry\Users\UserExistsException;
use GrahamCampbell\Binput\Facades\Binput;
use GrahamCampbell\Credentials\Facades\Credentials;
use GrahamCampbell\Credentials\Facades\UserRepository;
use GrahamCampbell\Throttle\Throttlers\ThrottlerInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

/**
 * This is the registration controller class.
 *
 * @author Graham Campbell <graham@mineuk.com>
 */
class RegistrationController extends AbstractController
{
    /**
     * The throttler instance.
     *
     * @var \GrahamCampbell\Throttle\Throttlers\ThrottlerInterface
     */
    protected $throttler;

    /**
     * Create a new instance.
     *
     * @param \GrahamCampbell\Throttle\Throttlers\ThrottlerInterface $throttler
     *
     * @return void
     */
    public function __construct(ThrottlerInterface $throttler)
    {
        $this->throttler = $throttler;

        $this->beforeFilter('throttle.register', ['only' => ['postRegister']]);

        parent::__construct();
    }

    /**
     * Display the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function getRegister()
    {
        return View::make('credentials::account.register');
    }

    /**
     * Attempt to register a new user.
     *
     * @return \Illuminate\Http\Response
     */
    public function postRegister()
    {
        if (!Config::get('credentials.regallowed')) {
            return Redirect::route('account.register');
        }

        $input = Binput::only(Config::get('credentials.register_inputs',['first_name', 'last_name', 'email', 'password', 'password_confirmation']));

        $val = UserRepository::validate($input, array_keys($input));
        if ($val->fails()) {
            return Redirect::route('account.register')->withInput()->withErrors($val->errors());
        }

        $this->throttler->hit();

        try {
            unset($input['password_confirmation']);

            $registeredUser = DB::transaction(function() use($input) {
                $user = Credentials::register($input);

                if (!Config::get('credentials.activation')) {

                    Credentials::notifyUserRegistered($user);

                    $user->attemptActivation($user->getActivationCode());
                    $user->addGroup(Credentials::getGroupProvider()->findByName('Users'));

                    return Redirect::to(Config::get('core.home', '/'))
                        ->with('success', trans('info.register.createOk'));
                }

                Credentials::notifyUserRegisteredActivation($user);

                return $user;

            });

            return Redirect::to(Config::get('core.register_redirect_url', '/'))
                ->with('registeredUser', $registeredUser)
                ->with('success', trans('info.register.createOkCheckEmail'));

        } catch (UserExistsException $e) {
            return Redirect::route('account.register')->withInput()->withErrors($val->errors())
                ->with('error', trans('info.register.emailAlreadyRegistered'));
        }
    }


}

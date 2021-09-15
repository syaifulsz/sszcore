<?php

namespace sszcore\components;

use sszcore\traits\ConfigPropertyTrait;

/**
 * Class Mailer
 * @package sszcore\components
 * @since 0.2.6
 */
class Mailer
{
    public $attachments;
    public $from = [];
    public $error;

    use ConfigPropertyTrait;

    /**
     * @param array $configs
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __construct( array $configs = [] )
    {
        $componentConfig = $this->initializeComponentConfig( $configs );

        $this->from = $this->config->get( 'mailgun.from', [] );

        if ( ( $name = $this->config->get( 'mailgun.from_name' ) ) && ( $email = $this->config->get( 'mailgun.from_email' ) ) ) {
            $this->from = [
                $email => $name
            ];
        }

        if ( !empty( $configs[ 'from' ] ) ) {
            $this->from = $configs[ 'from' ];
        }
    }

    /**
     * @return \Swift_SmtpTransport
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function transport()
    {
        return ( new \Swift_SmtpTransport(
            $this->config->get( 'mailgun.host' ),
            $this->config->get( 'mailgun.port' ),
            ( $this->config->get( 'mailgun.tls' ) ? 'tls' : null )
        ) )
            ->setAuthMode( 'login' )
            ->setUsername( $this->config->get( 'mailgun.username' ) )
            ->setPassword( $this->config->get( 'mailgun.password' ) );
    }

    /**
     * @param array $attachments
     */
    public function setAttachments( array $attachments )
    {
        $this->attachments = $attachments;
    }

    /**
     * @param $attachment
     * @param string $name
     * @param string $mimeType
     */
    public function addAttachment( $attachment, string $name = 'Attachment', string $mimeType = 'application/pdf' )
    {
        $this->attachments[] = new \Swift_Attachment( $attachment, $name, $mimeType );
    }

    /**
     * @param array $from
     */
    public function setFrom( array $from )
    {
        $this->from = $from;
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addFrom( string $email, string $name = '' )
    {
        $this->from[ $email ] = $name ?: $email;
    }

    /**
     * @param string $title
     * @param array $to
     * @param string $body
     * @param null $attachment
     * @return false|int
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function send( string $title, array $to, string $body = '', $attachment = null )
    {
        $body = $body ?: 'This is an automated email send from ' . $this->config->getAppConfig( 'name' ) . '.';

        $mailer = new \Swift_Mailer( $this->transport() );
        $message = ( new \Swift_Message( $title ) )
            ->setContentType( 'text/html' )
            ->setTo( $to )
            ->setBody( $body );

        if ( $this->from ) {
            $message->setFrom( $this->from );
        }

        if ( $attachment ) {
            $this->addAttachment( $attachment );
        }

        if ( $this->attachments ) {
            foreach ( $this->attachments as $att ) {
                $message->attach( $att );
            }
        }

        try {
            return $mailer->send( $message );
        } catch ( \Swift_TransportException $e ) {
            $this->error = $e->getMessage();
            return false;
        } catch ( \Swift_SwiftException $e ) {
            $this->error = $e->getMessage();
            return false;
        } catch ( \Exception $e ) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}

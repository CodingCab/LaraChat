<?php

namespace App\Modules\Webhooks\src\Services;

use App\Modules\Webhooks\src\Models\WebhooksConfiguration;
use AWS;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Sns\SnsClient;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class SnsController.
 */
class SnsService
{
    /**
     * @var SnsClient
     */
    public $awsSnsClient;

    private string $topicName;

    public AwsException $lastException;

    public Result $lastResponse;

    public static function client()
    {
        return (new SnsService(''))->awsSnsClient;
    }

    /**
     * SnsController constructor.
     */
    public function __construct(string $topicName = '')
    {
        $this->awsSnsClient = AWS::createClient('sns');

        $this->topicName = $topicName;
    }

    public static function getConfiguration(): WebhooksConfiguration
    {
        /** @var WebhooksConfiguration $model */
        $model = WebhooksConfiguration::query()->firstOrCreate([], []);

        return $model;
    }

    /**
     * @return array|Result
     */
    public static function subscribeOrFail(string $endpoint)
    {
        $subscribeRequestData = [
            'Protocol' => 'https',
            'Endpoint' => $endpoint,
            'ReturnSubscriptionArn' => true,
            'TopicArn' => self::getConfiguration()->topic_arn,
        ];

        try {
            $subscribeResponse = self::client()->subscribe($subscribeRequestData);
        } catch (AwsException $awsException) {
            $subscribeResponse = [
                'service' => 'AWS SNS',
                'data_sent' => $subscribeRequestData,
                'data_received' => [
                    'status_code' => $awsException->getStatusCode(),
                    'message' => $awsException->getMessage(),
                ],
            ];
            Log::error('Could not subscribe to AWS SNS topic', $subscribeResponse);

            response()->json(
                ['message' => $subscribeResponse],
                $awsException->getStatusCode()
            )->throwResponse();
        }

        return $subscribeResponse;
    }

    /**
     * @return mixed
     */
    public function listSubscriptions()
    {
        return $this->awsSnsClient->listSubscriptions();
    }

    public function createTopic(?string $topic_name = null): bool
    {
        if ($topic_name === null) {
            $topic_name = $this->getFullTopicName();
        }

        try {
            $this->awsSnsClient->createTopic(['Name' => $topic_name]);
        } catch (AwsException $e) {
            $this->lastException = $e;
            Log::critical('Could not create SNS topic', ['code' => $e->getStatusCode(), 'message' => $e->getMessage()]);

            return false;
        }

        return true;
    }

    public static function subscribeToTopic(string $subscription_url): bool
    {
        self::client()->subscribe([
            'Protocol' => 'https',
            'Endpoint' => trim($subscription_url),
            'ReturnSubscriptionArn' => true,
            'TopicArn' => self::getConfiguration()->topic_arn,
        ]);

        return true;
    }

    /**
     * @param  null  $topic_name
     */
    public function publish(string $message, $topic_name = null): bool
    {
        if (is_null($this->awsSnsClient)) {
            return false;
        }

        if ($topic_name === null) {
            $topic_name = self::getFullTopicName($this->topicName);
        }

        $notification = [
            'TargetArn' => self::getTopicArn($topic_name),
            'Message' => $message,
        ];

        try {
            $this->lastResponse = $this->awsSnsClient->publish($notification);
            Log::debug('AwsSns: message published', $this->lastResponse->toArray());

            return true;
        } catch (AwsException $e) {
            Log::error('Could not publish SNS message', [
                'code' => $e->getStatusCode(),
                'return_message' => $e->getMessage(),
                'topic' => self::getTopicArn($topic_name),
                'message' => $notification,
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Could not publish SNS message', [
                'code' => $e->getCode(),
                'return_message' => $e->getMessage(),
                'topic' => self::getTopicArn($topic_name),
                'message' => $notification,
            ]);

            return false;
        }
    }

    /**
     * @return Result
     *
     * "MessageAttributes" => [
     *   "sampleAttributeName" => [
     *     "DataType" => "String",
     *     "StringValue" => "sampleAttributeValue"
     *   ],
     * ],
     */
    public static function publishNew(string $message, ?array $message_attributes = null): Result
    {
        $notification = [
            'TargetArn' => self::getConfiguration()->topic_arn,
            'Message' => $message,
        ];

        if ($message_attributes) {
            $notification['MessageAttributes'] = $message_attributes;
        }

        return self::client()->publish($notification);
    }

    /**
     * @param  null  $topic_name
     */
    public function deleteTopic($topic_name = null): bool
    {
        try {
            $this->awsSnsClient->deleteTopic([
                'TopicArn' => self::getTopicArn($topic_name),
            ]);
        } catch (AwsException $e) {
            Log::critical('Could not delete SNS topic', ['code' => $e->getStatusCode(), 'message' => $e->getMessage()]);

            return false;
        }

        return true;
    }

    public static function getFullTopicName(string $topic_name): string
    {
        return config('sns.topic.prefix', '').$topic_name;
    }

    public static function getTopicArn(?string $topic_name = null): string
    {
        return implode(':', [
            'arn',
            'aws',
            'sns',
            config('aws.region'),
            config('aws.user_code'),
            $topic_name,
        ]);
    }
}

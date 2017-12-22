<?php

namespace Test;

use Appkr\SwaggerPocApi\ApiException;
use Appkr\SwaggerPocApi\Model\ErrorDto;
use Appkr\SwaggerPocApi\Model\LoginRequest;
use Appkr\SwaggerPocApi\ObjectSerializer;
use Appkr\SwaggerPocApi\Service\AuthApi;
use Appkr\SwaggerPocApi\Service\ProductApi;
use Appkr\SwaggerPocApi\Service\ReviewApi;
use Exception;
use Illuminate\Support\Debug\Dumper;

abstract class SwaggerPocApiTester extends TestCase
{
    /**
     * AuthApi 인스턴스를 구합니다.
     *
     * @return AuthApi
     */
    public function getAuthApi()
    {
        return $this->app->make(AuthApi::class);
    }

    /**
     * ProductApi 인스턴스를 구합니다.
     *
     * @return ProductApi
     */
    public function getProductApi()
    {
        return $this->app->make(ProductApi::class);
    }

    /**
     * ReviewApi 인스턴스를 구합니다.
     *
     * @return ReviewApi
     */
    public function getReviewApi()
    {
        return $this->app->make(ReviewApi::class);
    }

    /**
     * LoginRequest 인스턴스를 구합니다.
     *
     * @param array $overrides {
     *     @var string $email
     *     @var string $password
     * }
     * @return LoginRequest
     */
    public function getLoginRequest(array $overrides = [])
    {
        $userName = $overrides['email'] ?? env('SWAGGER_POC_API_USERNAME', 'user@example.com');
        $userPass = $overrides['password'] ?? env('SWAGGER_POC_API_PASSWORD', 'secret');

        return new LoginRequest([
            'email' => $userName,
            'password' => $userPass,
        ]);
    }

    /**
     * 로그인하고 AccessToken 인스턴스를 구합니다.
     *
     * @param LoginRequest $loginRequest
     * @return \Appkr\SwaggerPocApi\Model\AccessToken
     * @throws Exception
     */
    public function login(LoginRequest $loginRequest = null)
    {
        $loginRequest = $loginRequest ?: $this->getLoginRequest();

        try {
            $accessToken = $this->getAuthApi()->login($loginRequest);
        } catch (ApiException $e) {
            $content = json_decode($e->getResponseBody());
            $errorDto = ObjectSerializer::deserialize($content, ErrorDto::class);
            $e->setResponseObject($errorDto);
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }

        return $accessToken;
    }

    /**
     * 로그인하고 Authorization 헤더로 사용할 문자열을 구합니다.
     *
     * @param LoginRequest|null $loginRequest
     * @return string
     */
    public function getAuthString(LoginRequest $loginRequest = null)
    {
        $accessToken = $this->login($loginRequest);

        return "{$accessToken->getTokenType()} {$accessToken->getAccessToken()}";
    }

    /**
     * 변수를 덤프합니다.
     *
     * @param array ...$args
     */
    public function dump(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x);
        }
    }
}
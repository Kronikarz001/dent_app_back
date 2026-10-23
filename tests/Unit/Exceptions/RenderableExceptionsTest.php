<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\DefaultMessageGroupException;
use App\Exceptions\DuplicateCompanyDataException;
use App\Exceptions\DuplicateUserDataException;
use App\Exceptions\FileableTypeException;
use App\Exceptions\FileUploadException;
use App\Exceptions\InvalidAuditableIdentifierException;
use App\Exceptions\MessageAccessDeniedException;
use App\Exceptions\MessageGroupAccessDeniedException;
use App\Exceptions\MessageGroupMemberNotFoundException;
use App\Exceptions\MessageGroupMinimumMembersException;
use App\Exceptions\P1IntegrationNotImplementedException;
use App\Exceptions\PermissionDeniedException;
use App\Exceptions\PhoneNumberAlreadyAssignedException;
use App\Exceptions\ScheduleConflictException;
use Exception;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

/**
 * Summary of RenderableExceptionsTest
 */
final class RenderableExceptionsTest extends UnitTestCase
{
    /**
     * @return array<string, array{Exception, string, int}>
     */
    public static function defaultsProvider(): array
    {
        return [
            'default message group' => [new DefaultMessageGroupException, 'Nie można edytować grupy domyślnej.', 403],
            'fileable type' => [new FileableTypeException, 'Bad fileable type', 400],
            'file upload' => [new FileUploadException, 'Error with upload file', 500],
            'invalid auditable identifier' => [new InvalidAuditableIdentifierException, 'Invalid auditable identifier', 404],
            'message access denied' => [new MessageAccessDeniedException, 'Można usuwać tylko własne wiadomości.', 403],
            'message group access denied' => [new MessageGroupAccessDeniedException, 'Nie jesteś członkiem tej grupy.', 403],
            'message group member not found' => [new MessageGroupMemberNotFoundException, 'Użytkownik nie jest członkiem tej grupy.', 404],
            'message group minimum members' => [new MessageGroupMinimumMembersException, 'Grupa musi mieć co najmniej 2 członków.', 403],
            'p1 not implemented' => [new P1IntegrationNotImplementedException, 'Integracja z P1 nie jest jeszcze podłączona.', 501],
            'permission denied' => [new PermissionDeniedException, 'Nie masz uprawnień do wykonania tej akcji.', 403],
            'phone number already assigned' => [new PhoneNumberAlreadyAssignedException, 'Ten numer telefonu jest już przypisany do innego użytkownika lub pacjenta.', 409],
            'schedule conflict' => [new ScheduleConflictException, 'Wybrany termin koliduje z już istniejącym wpisem w kalendarzu.', 409],
            'duplicate company data' => [new DuplicateCompanyDataException('NIP jest już zajęty.'), 'NIP jest już zajęty.', 409],
            'duplicate user data' => [new DuplicateUserDataException('PESEL jest już zajęty.'), 'PESEL jest już zajęty.', 409],
        ];
    }

    /**
     * @return array<string, array{class-string<Exception>, int}>
     */
    public static function customMessageProvider(): array
    {
        return [
            'default message group' => [DefaultMessageGroupException::class, 403],
            'fileable type' => [FileableTypeException::class, 400],
            'file upload' => [FileUploadException::class, 500],
            'invalid auditable identifier' => [InvalidAuditableIdentifierException::class, 404],
            'message access denied' => [MessageAccessDeniedException::class, 403],
            'message group access denied' => [MessageGroupAccessDeniedException::class, 403],
            'message group member not found' => [MessageGroupMemberNotFoundException::class, 404],
            'message group minimum members' => [MessageGroupMinimumMembersException::class, 403],
            'p1 not implemented' => [P1IntegrationNotImplementedException::class, 501],
            'permission denied' => [PermissionDeniedException::class, 403],
            'phone number already assigned' => [PhoneNumberAlreadyAssignedException::class, 409],
            'schedule conflict' => [ScheduleConflictException::class, 409],
        ];
    }

    /**
     * @param Exception $exception
     * @param string $message
     * @param int $status
     * @return void
     */
    #[DataProvider('defaultsProvider')]
    public function testExceptionHasDefaultMessageAndCode(Exception $exception, string $message, int $status): void
    {
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($status, $exception->getCode());
    }

    /**
     * @param Exception $exception
     * @param string $message
     * @param int $status
     * @return void
     */
    #[DataProvider('defaultsProvider')]
    public function testRenderReturnsJsonMessageWithExceptionStatus(Exception $exception, string $message, int $status): void
    {
        $response = $exception->render(Request::create('/'));

        $this->assertSame($status, $response->getStatusCode());
        $this->assertSame(['message' => $message], $response->getData(true));
    }

    /**
     * @param class-string<Exception> $class
     * @param int $status
     * @return void
     */
    #[DataProvider('customMessageProvider')]
    public function testCustomMessageKeepsStatus(string $class, int $status): void
    {
        $exception = new $class('Własny komunikat');

        $this->assertSame('Własny komunikat', $exception->getMessage());
        $this->assertSame($status, $exception->render(Request::create('/'))->getStatusCode());
    }
}

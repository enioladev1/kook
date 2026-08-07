<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('audit logs cannot be updated through the model', function () {
    $log = AuditLog::create(['action' => 'test.action']);

    expect(fn () => $log->update(['action' => 'tampered']))
        ->toThrow(LogicException::class);
});

test('audit logs cannot be deleted through the model', function () {
    $log = AuditLog::create(['action' => 'test.action']);

    expect(fn () => $log->delete())
        ->toThrow(LogicException::class);
});

test('the database rejects direct updates to audit logs', function () {
    $log = AuditLog::create(['action' => 'test.action']);

    expect(fn () => DB::table('audit_logs')->where('id', $log->id)->update(['action' => 'tampered']))
        ->toThrow(Exception::class, 'append-only');
});

test('the database rejects direct deletes of audit logs', function () {
    $log = AuditLog::create(['action' => 'test.action']);

    expect(fn () => DB::table('audit_logs')->where('id', $log->id)->delete())
        ->toThrow(Exception::class, 'append-only');
});

test('audit logs have no updated_at column', function () {
    $log = AuditLog::create(['action' => 'test.action']);

    expect($log->getAttributes())->not->toHaveKey('updated_at');
});

test('an audit log can reference a user without exposing sensitive relations by default', function () {
    $user = User::factory()->create();
    $log = AuditLog::create(['user_id' => $user->id, 'action' => 'login.success']);

    expect($log->user->id)->toBe($user->id);
});

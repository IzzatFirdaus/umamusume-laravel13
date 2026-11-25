<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * §1.9 prints two figures per member: her stat line and, beside it, the caps the client shows
 * (`Speed / Stamina / Power / Guts / Wit, then the caps the client shows`). Only the stat line was
 * stored, so a member trained to her ceiling and a member with room left read identically.
 *
 * **These are readings, not a formula.** The cap on a Unity Cup member is the client's own number.
 * §1.9 lists caps between 700 and 1140 and states no relation to grade, level or aptitudes, so
 * nothing here computes one: an unrecorded cap stays null (D-220), because `0` would read as a
 * member who can gain nothing.
 *
 * The columns are `unsignedSmallInteger`, not the unsigned tiny integer the stat line uses. A
 * member's cap exceeds her stat — §1.9's highest Guts line is 1068 against a 1140 cap — so an
 * 8-bit column cannot hold the figure on any driver that honours it. SQLite ignores column width,
 * which is why the existing stat columns hold 1068 today; MySQL and PostgreSQL would truncate it.
 * The stat line is widened in the same pass for the same reason, because a reading that only fits
 * one driver is a schema bug that SQLite hides.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roster_members', function (Blueprint $table): void {
            // The five cap readings. Nullable: the Team tab prints them, but a member the Trainer
            // adds by name has no caps until she is read off the client, and absent is not zero.
            $table->unsignedSmallInteger('speed_cap')->nullable()->after('wit');
            $table->unsignedSmallInteger('stamina_cap')->nullable()->after('speed_cap');
            $table->unsignedSmallInteger('power_cap')->nullable()->after('stamina_cap');
            $table->unsignedSmallInteger('guts_cap')->nullable()->after('power_cap');
            $table->unsignedSmallInteger('wit_cap')->nullable()->after('guts_cap');
        });
    }

    public function down(): void
    {
        Schema::table('roster_members', function (Blueprint $table): void {
            $table->dropColumn(['speed_cap', 'stamina_cap', 'power_cap', 'guts_cap', 'wit_cap']);
        });
    }
};

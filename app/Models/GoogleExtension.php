<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoogleExtension extends Model
{
    use HasFactory;

    protected $table = 'extension_fingerprints';

    protected $primaryKey = 'id';

    protected $fillable = [ 'user_id', 'updated_at' ];

    public function isExtensionBound(): bool
    {
        return !!$this->user_id;
    }

    public function bindExtension( $user_id )
    {
        $this->update([
            'user_id' => $user_id,
            'updated_at' => today()
        ]);
    }

    public static function saveNew(Request $request): GoogleExtension
    {
        $extension_id =  DB::table('extension_fingerprints')
            ->insertGetId([
                'extension_id' =>  $request->get('extension_id'),
                'user_id' =>  $request->get('user_id'),
                'fingerprint' =>  $request->get('fingerprint'),
                'languages' =>  json_encode($request->get('languages')),
                'screen_resolution' =>  json_encode($request->screenResolution),
                'platform' =>  $request->get('platform'),
                'vendor' =>  $request->get('vendor'),
                'timezone' =>  $request->get('timezone'),
                'created_at' =>  DB::raw('now()'),
            ],'extension_id');

        return GoogleExtension::where('extension_id', '=', $extension_id)->first();
    }

    public function disable(): ?GoogleExtension
    {
        $this->is_blocked = true;
        $this->save();
        return $this->fresh();
    }

    public function unblock(): ?GoogleExtension
    {
        $this->removeDuplicates();
        $this->is_blocked = false;
        $this->save();
        return $this->fresh();
    }

    public function reset(): ?bool
    {
        $this->removeDuplicates();
        return $this->delete();
    }

    public function removeDuplicates(): void
    {
        $duplicates = $this->checkDuplicates($this->user_id);
        if (!empty($duplicates)) {
            self::whereIn('id', $duplicates)
                ->each(function ($extension) {
                    $extension->delete();
                });
        }
    }

    public function checkDuplicates($user_id)
    {
        $exclude = $this->id;
        return GoogleExtension::where('user_id', $user_id)
            ->when(
                $exclude,
                function ($q) use ($exclude) {
                    $q->where('id', '<>', $exclude);
                }
            )
            ->pluck('id')
            ->toArray();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ExtensionActivities::class, 'extension_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($extension) {
            $extension->activities()->delete();
        });
    }
}

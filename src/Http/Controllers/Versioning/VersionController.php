<?php

namespace GetCandy\Api\Http\Controllers\Versioning;

use Carbon\Carbon;
use GetCandy\Api\Core\Utils\Import\Models\Import;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use NeonDigital\Versioning\Version;
use Hashids;
use Versioning;

class VersionController extends BaseController
{
    public function restore($id, Request $request)
    {
        // Get the real id.
        $id = Hashids::decode($id)[0] ?? null;

        if (!$id) {
            return $this->errorNotFound();
        }

        $version = Version::findOrFail($id);
        return \DB::transaction(function () use ($version) {
            $result = Versioning::with('products')->restore($version);
            return response()->json([
                'id' => $result->encoded_id,
            ]);
        });
    }
}

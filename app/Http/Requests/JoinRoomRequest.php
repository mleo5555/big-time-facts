<?php

namespace App\Http\Requests;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Support\RoomCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JoinRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Anyone may try to join; the code itself is the "key" to a room.
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Players type codes on their phones: " kxqm " should match room "KXQM".
        // The is_string() check skips a missing code (let "required" report it) and
        // junk like code[]=x, which would crash normalize().
        if (is_string($this->input('code'))) {
            $this->merge([
                'code' => RoomCode::normalize($this->input('code')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                Rule::exists('rooms', 'code')->whereNot('status', RoomStatus::Finished->value),
            ],
            'nickname' => [
                'required',
                'string',
                'max:20',
                // Unique within this room only. If the code is wrong, room() is null and this
                // check is meaningless, but the code error is the one that matters then.
                Rule::unique('players', 'nickname')->where('room_id', $this->room()?->id),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.exists' => "We couldn't find an open room with that code.",
            'nickname.unique' => "That name's taken in this room. Try another!",
        ];
    }

    /**
     * The room being joined, looked up by the normalized code.
     *
     * Used by the rules above and by the controller. once() caches the result for
     * this request, so the room is only queried one time.
     */
    public function room(): ?Room
    {
        return once(fn () => Room::firstWhere('code', $this->input('code')));
    }
}

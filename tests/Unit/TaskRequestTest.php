<?php

namespace Tests\Unit;

use App\Http\Requests\TaskRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TaskRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authorize_returns_true()
    {
        $request = new TaskRequest();
        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_has_correct_validation_rules()
    {
        $request = new TaskRequest();
        $rules = $request->rules();

        $expectedRules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,completed',
            'order' => 'nullable|integer|min:0',
        ];

        $this->assertEquals($expectedRules, $rules);
    }

    /** @test */
    public function title_is_required()
    {
        $data = [
            'description' => 'Test description',
            'status' => 'pending',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function title_must_be_string()
    {
        $data = [
            'title' => 12345,
            'description' => 'Test description',
            'status' => 'pending',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function title_cannot_exceed_255_characters()
    {
        $data = [
            'title' => str_repeat('a', 256),
            'description' => 'Test description',
            'status' => 'pending',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function description_is_optional()
    {
        $data = [
            'title' => 'Test title',
            'status' => 'pending',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function description_can_be_null()
    {
        $data = [
            'title' => 'Test title',
            'description' => null,
            'status' => 'pending',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function description_must_be_string_when_provided()
    {
        $data = [
            'title' => 'Test title',
            'description' => 12345,
            'status' => 'pending',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    /** @test */
    public function description_cannot_exceed_1000_characters()
    {
        $data = [
            'title' => 'Test title',
            'description' => str_repeat('a', 1001),
            'status' => 'pending',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
    }

    /** @test */
    public function status_is_optional()
    {
        $data = [
            'title' => 'Test title',
            'description' => 'Test description',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function status_accepts_valid_values()
    {
        $validStatuses = ['pending', 'in_progress', 'completed'];

        foreach ($validStatuses as $status) {
            $data = [
                'title' => 'Test title',
                'status' => $status,
            ];

            $request = new TaskRequest();
            $validator = Validator::make($data, $request->rules());

            $this->assertTrue($validator->passes(), "Status '{$status}' should be valid");
        }
    }

    /** @test */
    public function status_rejects_invalid_values()
    {
        $invalidStatuses = ['invalid', 'done', 'active', 'cancelled'];

        foreach ($invalidStatuses as $status) {
            $data = [
                'title' => 'Test title',
                'status' => $status,
            ];

            $request = new TaskRequest();
            $validator = Validator::make($data, $request->rules());

            $this->assertFalse($validator->passes(), "Status '{$status}' should be invalid");
            $this->assertArrayHasKey('status', $validator->errors()->toArray());
        }
    }

    /** @test */
    public function order_is_optional()
    {
        $data = [
            'title' => 'Test title',
            'status' => 'pending',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function order_must_be_integer()
    {
        $data = [
            'title' => 'Test title',
            'order' => 'not-an-integer',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('order', $validator->errors()->toArray());
    }

    /** @test */
    public function order_must_be_zero_or_positive()
    {
        // Test valid values
        $validOrders = [0, 1, 5, 100];

        foreach ($validOrders as $order) {
            $data = [
                'title' => 'Test title',
                'order' => $order,
            ];

            $request = new TaskRequest();
            $validator = Validator::make($data, $request->rules());

            $this->assertTrue($validator->passes(), "Order '{$order}' should be valid");
        }

        // Test invalid negative value
        $data = [
            'title' => 'Test title',
            'order' => -1,
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('order', $validator->errors()->toArray());
    }

    /** @test */
    public function it_has_custom_validation_messages()
    {
        $request = new TaskRequest();
        $messages = $request->messages();

        $expectedMessages = [
            'title.required' => 'The title is required.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'description.max' => 'The description cannot exceed 1000 characters.',
            'status.in' => 'The status must be: pending, in progress or completed.',
            'order.integer' => 'The order must be an integer.',
            'order.min' => 'The order must be greater than or equal to 0.',
        ];

        $this->assertEquals($expectedMessages, $messages);
    }

    /** @test */
    public function validation_passes_with_all_valid_data()
    {
        $data = [
            'title' => 'Valid Task Title',
            'description' => 'Valid task description',
            'status' => 'in_progress',
            'order' => 5,
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
        $this->assertEmpty($validator->errors()->toArray());
    }

    /** @test */
    public function validation_passes_with_minimal_valid_data()
    {
        $data = [
            'title' => 'Valid Title',
        ];

        $request = new TaskRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
        $this->assertEmpty($validator->errors()->toArray());
    }
}

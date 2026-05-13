<?php


declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Presentation\Http\Controllers\Admin;

use DomainException;
use EvolutionCMS\FeatureFlags\Application\DTO\AdminFlagDTO;
use EvolutionCMS\FeatureFlags\Application\Exceptions\FlagValidationException;
use EvolutionCMS\FeatureFlags\Application\Validator\FlagValidator;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagAdminRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class FlagAdminController extends Controller
{
    public function __construct(
        private readonly FlagAdminRepositoryInterface $repository,
        private readonly FlagValidator                $validator
    )
    {
    }

    public function index(): View|JsonResponse
    {
        $flags = array_values($this->repository->list());

        $managerLang = $this->getManagerLanguage();

        return $this->respond(
            view: 'featureFlags::admin.flags.index',
            data: [
                'flags' => $flags,
                'managerLang' => $managerLang,
            ],
            json: ['data' => array_map(fn($dto) => $dto->toArray(), $flags)]
        );
    }

    public function create(): View
    {
        return view('featureFlags::admin.flags.form', ['flag' => null]);
    }

    public function edit(string $name): View|RedirectResponse
    {
        $flag = $this->repository->findByName($name);
        if (!$flag) {
            return redirect()->route('featureFlags::index')
                ->with('error', "Флаг '{$name}' не найден");
        }

        return view('featureFlags::admin.flags.form', ['flag' => $flag]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (!$this->repository->isWritable()) {
            return $this->respond(
                redirect: back()->with('error', 'Хранилище флагов в режиме только для чтения'),
                json: ['message' => 'Read-only mode'],
                statusCode: 503
            );
        }

        $input = $request->all();
        try {
            $this->validator->validateCreate($input);
        } catch (FlagValidationException $e) {
            return $this->respond(
                redirect: back()->withInput()->withErrors($e->errors),
                json: ['errors' => $e->errors],
                statusCode: 422
            );
        }

        try {
            $dto = AdminFlagDTO::fromArray($input);
            $this->repository->create($dto);
        } catch (DomainException $e) {
            return $this->respond(
                redirect: back()->withInput()->withErrors(['name' => $e->getMessage()]),
                json: ['errors' => ['name' => [$e->getMessage()]]],
                statusCode: 422
            );
        }

        return $this->respond(
            redirect: redirect()->route('featureFlags::index')->with('success', 'Флаг создан'),
            json: ['data' => $dto->toArray()],
            statusCode: 201
        );
    }

    /**
     * @throws \JsonException
     */
    public function update(string $name, Request $request): RedirectResponse|JsonResponse
    {
        if (!$this->repository->isWritable()) {
            return $this->respond(
                redirect: back()->with('error', 'Хранилище флагов в режиме только для чтения'),
                json: ['message' => 'Read-only mode'],
                statusCode: 503
            );
        }

        $input = $request->all();

        try {
            $this->validator->validateUpdate($input);
        } catch (FlagValidationException $e) {
            return $this->respond(
                redirect: back()->withInput()->withErrors($e->errors),
                json: ['errors' => $e->errors],
                statusCode: 422
            );
        }

        $existing = $this->repository->findByName($name);
        if (!$existing) {
            return $this->respond(
                redirect: redirect()->route('featureFlags::index')->with('error', "Флаг '{$name}' не найден"),
                json: ['message' => "Flag '{$name}' not found"],
                statusCode: 404
            );
        }

        $merged = array_merge($existing->toArray(), $input);
        $dto = AdminFlagDTO::fromArray($merged);
        $this->repository->update($name, $dto);

        return $this->respond(
            redirect: redirect()->route('featureFlags::index')->with('success', 'Флаг обновлён'),
            json: ['data' => $dto->toArray()]
        );
    }

    public function destroy(string $name): RedirectResponse|JsonResponse
    {
        if (!$this->repository->isWritable()) {
            return $this->respond(
                redirect: back()->with('error', 'Хранилище флагов в режиме только для чтения'),
                json: ['message' => 'Read-only mode'],
                statusCode: 503
            );
        }

        $this->repository->delete($name);

        return $this->respond(
            redirect: redirect()->route('featureFlags::index')->with('success', 'Флаг удалён'),
            json: null,
            statusCode: 204
        );
    }

    public function indexApi(): JsonResponse
    {
        return $this->index();
    }

    public function storeApi(Request $request): JsonResponse
    {
        return $this->store($request);
    }

    public function updateApi(string $name, Request $request): JsonResponse
    {
        return $this->update($name, $request);
    }

    public function destroyApi(string $name): JsonResponse
    {
        return $this->destroy($name);
    }

    /**
     * Универсальный ответ: либо View + Redirect, либо JSON
     */
    private function respond(
        ?string           $view = null,
        array             $data = [],
        ?RedirectResponse $redirect = null,
        ?array            $json = null,
        int               $statusCode = 200
    ): View|RedirectResponse|JsonResponse
    {
        // Если запрос JSON (Accept header или ?api=1)
        if (request()->wantsJson() || request()->boolean('api')) {
            return new JsonResponse($json ?? [], $statusCode);
        }

        // Если передан редирект — возвращаем его
        if ($redirect) {
            return $redirect;
        }

        // Иначе — возвращаем представление
        return $view ? view($view, $data) : new JsonResponse($json ?? [], $statusCode);
    }

    private function getManagerLanguage(): string
    {
        $lang = evo()->getConfig('manager_language')
            ?? evo()->getConfig('cultureKey')
            ?? 'en';

        // Нормализуем: 'ru-UTF8' -> 'ru', 'en-US' -> 'en'
        return preg_replace('/[-_].*$/', '', strtolower($lang));
    }
}

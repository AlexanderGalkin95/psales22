<?php

namespace App\Extensions\Authorization;

/**
 * Ps - Permissions
 */
class Ps
{
    const EXAMPLE = 'example';
    const EXAMPLE_ADDED = 'example-added';
    const EXAMPLE_CHILD = 'example-child';


    const FORM_RATING = 'form-rating';
    const FORM_RATING_VIEW = 'form-rating-view';

    const CRITERION_RATING = 'criterion-rating';
    const CRITERION_RATING_VIEW = 'criterion-rating-view';

    const DASHBOARD = 'dashboard';
    const DASHBOARD_VIEW = 'dashboard-view';

    const REPORT_SETT = 'report-setting';
    const REPORT_SETT_VIEW = 'report-setting-view';

    const REPORT_SEND_SETT = 'report-send-setting';

    const CALIBR_SESS = 'calibration-session';
    const CALIBR_SESS_VIEW = 'calibration-session-view';

    // Коммуникации: звонки и т.д.
    const COMM = 'communication';
    const COMM_VIEW = 'communication-view';
    const COMM_UPDATE = 'communication-update';
    const COMM_DELETE = 'communication-delete';

    // Статусы коммуникаций
    const COMM_STATUS = 'communication-status';
    const COMM_STATUS_VIEW = 'communication-status-view';

    // Оценки коммуникаций
    const COMM_RATING = 'communication-rating';
    const COMM_RATING_VIEW = 'communication-rating-view';
    const COMM_RATING_CREATE = 'communication-rating-create';
    const COMM_RATING_UPDATE = 'communication-rating-update';
    const COMM_RATING_UPDATE_SELF = 'communication-rating-update-self';
    const COMM_RATING_UPDATE_SELF_GROUP = 'communication-rating-update-self-group';
    const COMM_RATING_DELETE = 'communication-rating-delete';

    const TAG = 'tag';

    const COMPANY = 'company';
    const COMPANY_VIEW = 'company-view';
    const COMPANY_VIEW_SELF = 'company-view-self';
    const COMPANY_CREATE = 'company-create';
    const COMPANY_UPDATE = 'company-update';
    const COMPANY_DELETE = 'company-delete';

    const PROJECT = 'project';
    const PROJECT_VIEW = 'project-view';
    const PROJECT_VIEW_SELF = 'project-view-self';
    const PROJECT_CREATE = 'project-create';
    const PROJECT_UPDATE = 'project-update';
    const PROJECT_DELETE = 'project-delete';

    const PROJECT_EO = 'project-eo';
    const PROJECT_EO_VIEW = 'project-eo-view';
    const PROJECT_EO_CREATE = 'project-eo-create';
    const PROJECT_EO_UPDATE = 'project-eo-update';
    const PROJECT_EO_DELETE = 'project-eo-delete';


    const USER = 'user';
    const USER_VIEW = 'user-view';
    const USER_CREATE = 'user-create';
    const USER_UPDATE = 'user-update';
    const USER_DELETE = 'user-delete';

    const INTEGRATION = 'integration';

    //todo под вопросом - что сюда входит?
    const LK = 'lk';

    //todo под вопросом - что сюда входит?
    const ADMIN_PANEL = 'admin-panel';


    public static function config(): array
    {
        return [
//            Ps::EXAMPLE => [
//                'name' => 'Доступ к чему-то',
//                'condition' => (fn() => true),
//                'added' => [Ps::EXAMPLE_ADDED],
//                'child' => [Ps::EXAMPLE_CHILD],
//            ],
//            Ps::EXAMPLE_ADDED => [
//                'name' => 'Доступ к чему-то по условию',
//                'condition' => function ($user, $model) {
//                    return (bool)$user->id === $model->user_id;
//                }
//            ],
//            Ps::EXAMPLE_CHILD => [
//                'name' => 'Доступ к под-чему-то, который разрешен, если есть доступ к чему-то',
//                'parent' => Ps::EXAMPLE,
//            ],

            Ps::FORM_RATING => ['name' => 'Полный доступ к формам оценки', 'child' => [Ps::FORM_RATING_VIEW]],
            Ps::FORM_RATING_VIEW => ['name' => 'Просмотр форм оценки', 'parent' => Ps::FORM_RATING],

            Ps::CRITERION_RATING => ['name' => 'Полный доступ к критериям оценки', 'child' => [Ps::CRITERION_RATING_VIEW]],
            Ps::CRITERION_RATING_VIEW => ['name' => 'Просмотр критериев оценки', 'parent' => Ps::CRITERION_RATING],

            Ps::DASHBOARD => ['name' => 'Полный доступ к дашборду/аналитике', 'child' => [Ps::DASHBOARD_VIEW]],
            Ps::DASHBOARD_VIEW => ['name' => 'Доступ к просмотру дашборда/аналитики', 'parent' => Ps::DASHBOARD],

            Ps::REPORT_SETT => ['name' => 'Полный доступ к настройкам отчетов', 'child' => [Ps::REPORT_SETT_VIEW]],
            Ps::REPORT_SETT_VIEW => ['name' => 'Доступ к просмотру настроек отчетов', 'parent' => Ps::REPORT_SETT],

            Ps::REPORT_SEND_SETT => ['name' => 'Полный доступ к настройкам отправки отчетов'],

            Ps::CALIBR_SESS => ['name' => 'Полный доступ к калибровочным сессиям', 'child' => [Ps::CALIBR_SESS_VIEW]],
            Ps::CALIBR_SESS_VIEW => ['name' => 'Доступ к просмотру калибровочных сессий', 'parent' => Ps::CALIBR_SESS],

            Ps::TAG => ['name' => 'Полный доступ к тегам'],

            Ps::COMPANY => [
                'name' => 'Полный доступ к компаниям',
                'child' => [Ps::COMPANY_VIEW, Ps::COMPANY_CREATE, Ps::COMPANY_UPDATE, Ps::COMPANY_DELETE]
            ],
            Ps::COMPANY_VIEW => ['name' => 'Просмотр компании', 'parent' => Ps::COMPANY, 'added' => [Ps::COMPANY_VIEW_SELF]],
            Ps::COMPANY_VIEW_SELF => ['name' => 'Просмотр своей компании', 'parent' => Ps::COMPANY_VIEW],
            Ps::COMPANY_CREATE => ['name' => 'Создание компании', 'parent' => Ps::COMPANY],
            Ps::COMPANY_UPDATE => ['name' => 'Редактирование компании', 'parent' => Ps::COMPANY],
            Ps::COMPANY_DELETE => ['name' => 'Удаление компании', 'parent' => Ps::COMPANY],

            Ps::PROJECT => [
                'name' => 'Полный доступ к проектам',
                'child' => [Ps::PROJECT_VIEW, Ps::PROJECT_CREATE, Ps::PROJECT_UPDATE, Ps::PROJECT_DELETE]
            ],
            Ps::PROJECT_VIEW => ['name' => 'Просмотр проекта', 'parent' => Ps::PROJECT, 'added' => [Ps::PROJECT_VIEW_SELF]],
            Ps::PROJECT_VIEW_SELF => [
                'name' => 'Просмотр своего проекта',
                'parent' => Ps::PROJECT_VIEW,
                'condition' => (fn(PsContext $c) => (in_array($c->user->id, [$c->model->pm_id, $c->model->senior_id, $c->model->assessor_id]))),
            ],
            Ps::PROJECT_CREATE => ['name' => 'Создание проекта', 'parent' => Ps::PROJECT],
            Ps::PROJECT_UPDATE => ['name' => 'Редактирование проекта', 'parent' => Ps::PROJECT],
            Ps::PROJECT_DELETE => ['name' => 'Удаление проекта', 'parent' => Ps::PROJECT],

            Ps::PROJECT_EO => [
                'name' => 'Полный доступ к проектам ЕО',
                'child' => [Ps::PROJECT_EO_VIEW, Ps::PROJECT_EO_CREATE, Ps::PROJECT_EO_UPDATE, Ps::PROJECT_EO_DELETE]
            ],
            Ps::PROJECT_EO_VIEW => ['name' => 'Просмотр проекта ЕО', 'parent' => Ps::PROJECT_EO],
            Ps::PROJECT_EO_CREATE => ['name' => 'Создание проекта ЕО', 'parent' => Ps::PROJECT_EO],
            Ps::PROJECT_EO_UPDATE => ['name' => 'Редактирование проекта ЕО', 'parent' => Ps::PROJECT_EO],
            Ps::PROJECT_EO_DELETE => ['name' => 'Удаление проекта ЕО', 'parent' => Ps::PROJECT_EO],

            Ps::COMM_RATING => [
                'name' => 'Доступ к оценкам коммуникаций',
                'child' => [Ps::COMM_RATING_VIEW, Ps::COMM_RATING_CREATE, self::COMM_RATING_UPDATE, self::COMM_RATING_DELETE]
            ],

            Ps::COMM => ['name' => 'Полный доступ к коммуникациям', 'child' => [Ps::COMM_VIEW, Ps::COMM_UPDATE, Ps::COMM_DELETE]],
            Ps::COMM_VIEW => ['name' => 'Просмотр коммуникации', 'parent' => Ps::COMM],
            Ps::COMM_UPDATE => ['name' => 'Редактирование коммуникации', 'parent' => Ps::COMM],


            Ps::COMM_STATUS => ['name' => 'Полный доступ к статусам коммуникаций', 'child' => [Ps::COMM_STATUS_VIEW]],
            Ps::COMM_STATUS_VIEW => ['name' => 'Доступ к просмотру статусов коммуникаций', 'parent' => Ps::COMM_STATUS],

            Ps::COMM_RATING_VIEW => ['name' => 'Просмотр коммуникации', 'parent' => self::COMM_RATING],
            Ps::COMM_RATING_CREATE => ['name' => 'Создание коммуникации', 'parent' => self::COMM_RATING],
            Ps::COMM_RATING_UPDATE => [
                'name' => 'Редактирование коммуникации',
                'parent' => self::COMM_RATING,
                'added' => [Ps::COMM_RATING_UPDATE_SELF, self::COMM_RATING_UPDATE_SELF_GROUP]
            ],
            Ps::COMM_RATING_UPDATE_SELF => [
                'name' => 'Редактирование своей коммуникации',
                'parent' => self::COMM_RATING_UPDATE,
                'condition' => (fn(PsContext $c) => ($c->user->id == $c->model->user_id)),
            ],
            Ps::COMM_RATING_UPDATE_SELF_GROUP => [
                'name' => 'Редактирование коммуникации своей группы пользователей',
                'parent' => self::COMM_RATING_UPDATE,
                'condition' => (fn(PsContext $c) => (in_array($c->model->user_id, $c->usersIds))),
            ],
            Ps::COMM_RATING_DELETE => ['name' => 'Удаление коммуникации', 'parent' => self::COMM_RATING],


            Ps::USER => ['name' => 'Полный доступ к пользователям', 'child' => [Ps::USER_VIEW, Ps::USER_CREATE, Ps::USER_UPDATE, Ps::USER_DELETE]],
            Ps::USER_VIEW => ['name' => 'Просмотр пользователя', 'parent' => Ps::USER],
            Ps::USER_CREATE => ['name' => 'Создание пользователя', 'parent' => Ps::USER],
            Ps::USER_UPDATE => ['name' => 'Редактирование пользователя', 'parent' => Ps::USER],
            Ps::USER_DELETE => ['name' => 'Удаление/блокировка пользователя', 'parent' => Ps::USER],

            Ps::INTEGRATION => ['name' => 'Полный доступ к интеграциям'],

            Ps::LK => ['name' => 'Полный доступ к ЛК'],

            Ps::ADMIN_PANEL => ['name' => 'Полный доступ к панели администратора'],
        ];
    }


    public static function check(array $userPerms, string $perm, PsContext $context): bool
    {
        $data = Ps::config();

        // составляем список Разрешений, выполнение которых будет выполнять проверяемое, а именно: проверяемое, родительские, дополнительные
        $added = $data[$perm]['added'] ?? [];
        $parents = [];
        $parent = $data[$perm]['parent'] ?? null;
        while ($parent) {
            $parents[] = $parent;
            $parent = $data[$parent]['parent'] ?? null;
        };
        $perms = array_merge([$perm], $parents, $added);

        // из найденных есть смысл проверять только наличествующие у юзера
        $permsToCheck = array_intersect($userPerms, $perms);

        // если пусто, значит нет
        if (empty($permsToCheck)) {
            return false;
        }

        // безусловные надо проверять первыми, т.к. намного быстрее (условные для проверки могут делать что угодно)
        $nonConditionalPerms = [];
        $conditionalPerms = [];
        foreach ($permsToCheck as $perm) {
            if (key_exists('condition', $data[$perm])) {
                $conditionalPerms[] = $perm;
            } else {
                $nonConditionalPerms[] = $perm;
            }
        }

        // если есть безусловные, значит да
        if (!empty($nonConditionalPerms)) {
            return true;
        }

        // проверяем условные. Если хоть одно выполняется, да
        foreach ($conditionalPerms as $perm) {
            $condition = $data[$perm]['condition'];
            if ($condition($context)) {
                return true;
            }
        }

        // ничего не выполнилось
        return false;
    }




}

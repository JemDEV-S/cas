sistema-convocatorias/
│
├── app/
│   ├── Http/
│   │   └── Middleware/
│   │       ├── Authenticate.php
│   │       ├── EncryptCookies.php
│   │       ├── PreventRequestsDuringMaintenance.php
│   │       ├── RedirectIfAuthenticated.php
│   │       ├── TrimStrings.php
│   │       ├── TrustProxies.php
│   │       ├── ValidateSignature.php
│   │       └── VerifyCsrfToken.php
│   ├── Models/                         # ← Solo si necesitas modelos globales (raro)
│   ├── Providers/
│   │   └── AppServiceProvider.php      # ← Solo este por defecto en Laravel 11
│   └── View/
│       └── Components/                 # ← Si usas class-based components globales
│
├── bootstrap/
│   ├── app.php                         # ← Configuración principal (reemplaza Kernel.php)
│   ├── cache/
│   └── providers.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── logging.php
│   ├── mail.php
│   ├── modules.php                    # ← Configuración de nwidart/laravel-modules
│   ├── permission.php                 # ← Spatie Permission
│   ├── queue.php
│   ├── services.php
│   └── session.php
│
├── database/
│   ├── factories/
│   ├── migrations/                     # ← Migraciones globales (muy pocas o ninguna)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── ProductionSeeder.php        # ← Para datos iniciales en producción
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── robots.txt
│   ├── favicon.ico
│   ├── storage/                        # ← Link simbólico a storage/app/public
│   └── build/                          # ← Assets compilados (Vite)
│
├── resources/
│   ├── css/
│   │   ├── app.css                     # ← CSS global/compartido
│   │   └── admin.css                   # ← CSS específico de admin
│   ├── js/
│   │   ├── app.js                      # ← JS global/compartido
│   │   ├── bootstrap.js
│   │   └── Components/                 # ← Componentes Vue/React si usas
│   │       ├── Alert.vue
│   │       ├── Button.vue
│   │       └── Modal.vue
│   ├── lang/                           # ← Traducciones globales
│   │   ├── en/
│   │   │   ├── auth.php
│   │   │   ├── pagination.php
│   │   │   └── validation.php
│   │   └── es/
│   │       ├── auth.php
│   │       ├── pagination.php
│   │       └── validation.php
│   └── views/
│       ├── layouts/                    # ← LAYOUTS COMPARTIDOS
│       │   ├── app.blade.php          # ← Layout principal autenticado
│       │   ├── guest.blade.php        # ← Layout para no autenticados
│       │   ├── admin.blade.php        # ← Layout administrativo
│       │   └── print.blade.php        # ← Layout para impresión/PDF
│       ├── components/                 # ← COMPONENTES BLADE COMPARTIDOS
│       │   ├── alert.blade.php
│       │   ├── button.blade.php
│       │   ├── card.blade.php
│       │   ├── modal.blade.php
│       │   ├── breadcrumb.blade.php
│       │   ├── pagination.blade.php
│       │   └── form/
│       │       ├── input.blade.php
│       │       ├── select.blade.php
│       │       ├── textarea.blade.php
│       │       ├── checkbox.blade.php
│       │       ├── radio.blade.php
│       │       ├── file.blade.php
│       │       └── date.blade.php
│       ├── errors/                     # ← Páginas de error compartidas
│       │   ├── 401.blade.php
│       │   ├── 403.blade.php
│       │   ├── 404.blade.php
│       │   ├── 419.blade.php
│       │   ├── 429.blade.php
│       │   ├── 500.blade.php
│       │   └── 503.blade.php
│       └── emails/                     # ← Templates de email compartidos
│           ├── layout.blade.php
│           └── components/
│               ├── header.blade.php
│               ├── footer.blade.php
│               └── button.blade.php
│
├── routes/
│   ├── web.php                         # ← Rutas web principales (muy pocas)
│   ├── api.php                         # ← Rutas API principales (muy pocas)
│   ├── console.php                     # ← Comandos y schedules (reemplaza Console/Kernel.php)
│   └── channels.php                    # ← Broadcasting channels
│
├── storage/
│   ├── app/
│   │   ├── public/                     # ← Archivos públicos
│   │   ├── private/                    # ← Archivos privados
│   │   └── temp/                       # ← Archivos temporales
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   ├── testing/
│   │   └── views/
│   └── logs/
│
├── tests/
│   ├── Feature/                        # ← Tests generales de integración
│   ├── Unit/                           # ← Tests unitarios generales
│   └── TestCase.php
│
├── Modules/                            # ← MÓDULOS NWIDART
│   │
│   ├── Core/                           # ═══════════════════════════════════
│   │   │                               # MÓDULO CORE (Fundación)
│   │   ├── app/                        # ← Laravel 11: todo el código en /app
│   │   │   ├── Entities/               # ← Modelos base abstractos
│   │   │   │   ├── BaseModel.php
│   │   │   │   └── BaseSoftDelete.php
│   │   │   ├── Enums/                  # ← Enums compartidos
│   │   │   │   ├── Status.php
│   │   │   │   └── YesNo.php
│   │   │   ├── Exceptions/             # ← Excepciones personalizadas
│   │   │   │   ├── CoreException.php
│   │   │   │   ├── ValidationException.php
│   │   │   │   ├── BusinessRuleException.php
│   │   │   │   └── UnauthorizedException.php
│   │   │   ├── Helpers/                # ← Funciones helper
│   │   │   │   ├── helpers.php        # ← Cargado globalmente
│   │   │   │   ├── StringHelper.php
│   │   │   │   ├── DateHelper.php
│   │   │   │   ├── ArrayHelper.php
│   │   │   │   └── NumberHelper.php
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   ├── Middleware/
│   │   │   │   ├── Requests/
│   │   │   │   └── Resources/
│   │   │   ├── Providers/
│   │   │   │   └── CoreServiceProvider.php
│   │   │   ├── Repositories/
│   │   │   │   ├── Contracts/
│   │   │   │   │   └── BaseRepositoryInterface.php
│   │   │   │   └── Eloquent/
│   │   │   │       ├── BaseRepository.php
│   │   │   │       └── CacheRepository.php
│   │   │   ├── Services/               # ← Servicios base compartidos
│   │   │   │   ├── Contracts/
│   │   │   │   │   ├── FileServiceInterface.php
│   │   │   │   │   └── ValidationServiceInterface.php
│   │   │   │   ├── BaseService.php
│   │   │   │   ├── FileService.php
│   │   │   │   ├── ValidationService.php
│   │   │   │   └── EncryptionService.php
│   │   │   ├── Traits/                 # ← Traits compartidos
│   │   │   │   ├── HasUuid.php
│   │   │   │   ├── HasStatus.php
│   │   │   │   ├── HasMetadata.php
│   │   │   │   ├── Searchable.php
│   │   │   │   ├── Sortable.php
│   │   │   │   ├── Filterable.php
│   │   │   │   └── Exportable.php
│   │   │   ├── ValueObjects/           # ← Value Objects
│   │   │   │   ├── Email.php
│   │   │   │   ├── PhoneNumber.php
│   │   │   │   ├── DNI.php
│   │   │   │   ├── Money.php
│   │   │   │   └── DateRange.php
│   │   │   └── DTOs/                   # ← Data Transfer Objects
│   │   │       ├── PaginationDTO.php
│   │   │       ├── FilterDTO.php
│   │   │       └── SortDTO.php
│   │   ├── config/
│   │   │   └── config.php
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   ├── seeders/
│   │   │   └── factories/
│   │   ├── routes/
│   │   │   ├── api.php
│   │   │   └── web.php
│   │   ├── resources/
│   │   │   ├── views/
│   │   │   └── lang/
│   │   │       ├── en/
│   │   │       └── es/
│   │   ├── tests/
│   │   │   ├── Unit/
│   │   │   └── Feature/
│   │   ├── composer.json
│   │   └── module.json
│   │
│   ├── Configuration/                  # ═══════════════════════════════════
│   │   │                               # MÓDULO CONFIGURATION
│   │   ├── app/
│   │   │   ├── Entities/
│   │   │   │   ├── ConfigGroup.php
│   │   │   │   ├── SystemConfig.php
│   │   │   │   └── ConfigHistory.php
│   │   │   ├── Enums/
│   │   │   │   ├── ConfigValueType.php
│   │   │   │   └── ConfigInputType.php
│   │   │   ├── Events/
│   │   │   │   ├── ConfigUpdated.php
│   │   │   │   └── ConfigCacheCleared.php
│   │   │   ├── Facades/
│   │   │   │   └── Config.php          # ← Facade personalizado
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── ConfigGroupController.php
│   │   │   │   │   └── SystemConfigController.php
│   │   │   │   ├── Requests/
│   │   │   │   │   ├── StoreConfigGroupRequest.php
│   │   │   │   │   ├── UpdateConfigGroupRequest.php
│   │   │   │   │   └── UpdateSystemConfigRequest.php
│   │   │   │   └── Resources/
│   │   │   │       ├── ConfigGroupResource.php
│   │   │   │       └── SystemConfigResource.php
│   │   │   ├── Listeners/
│   │   │   │   └── ClearConfigCache.php
│   │   │   ├── Providers/
│   │   │   │   └── ConfigurationServiceProvider.php
│   │   │   ├── Repositories/
│   │   │   │   ├── Contracts/
│   │   │   │   │   └── ConfigRepositoryInterface.php
│   │   │   │   └── Eloquent/
│   │   │   │       └── ConfigRepository.php
│   │   │   └── Services/
│   │   │       ├── Contracts/
│   │   │       │   └── ConfigServiceInterface.php
│   │   │       ├── ConfigService.php
│   │   │       ├── CacheService.php
│   │   │       └── ValidationService.php
│   │   ├── config/
│   │   │   └── config.php
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   │   ├── 2024_01_01_000001_create_config_groups_table.php
│   │   │   │   ├── 2024_01_01_000002_create_system_configs_table.php
│   │   │   │   └── 2024_01_01_000003_create_config_history_table.php
│   │   │   ├── seeders/
│   │   │   │   ├── ConfigurationDatabaseSeeder.php
│   │   │   │   ├── ConfigGroupSeeder.php
│   │   │   │   └── SystemConfigSeeder.php
│   │   │   └── factories/
│   │   │       ├── ConfigGroupFactory.php
│   │   │       └── SystemConfigFactory.php
│   │   ├── routes/
│   │   │   ├── api.php
│   │   │   └── web.php
│   │   ├── resources/
│   │   │   ├── views/
│   │   │   │   ├── index.blade.php
│   │   │   │   ├── edit.blade.php
│   │   │   │   └── components/
│   │   │   │       ├── config-input.blade.php
│   │   │   │       └── config-group.blade.php
│   │   │   └── lang/
│   │   │       ├── en/
│   │   │       │   └── config.php
│   │   │       └── es/
│   │   │           └── config.php
│   │   ├── tests/
│   │   │   ├── Unit/
│   │   │   │   ├── ConfigServiceTest.php
│   │   │   │   └── ConfigValueObjectTest.php
│   │   │   └── Feature/
│   │   │       ├── ConfigGroupTest.php
│   │   │       └── SystemConfigTest.php
│   │   ├── composer.json
│   │   └── module.json
│   │
│   ├── Auth/                           # ═══════════════════════════════════
│   │   │                               # MÓDULO AUTH
│   │   ├── app/
│   │   │   ├── Entities/
│   │   │   │   ├── Role.php
│   │   │   │   ├── Permission.php
│   │   │   │   ├── UserSession.php
│   │   │   │   ├── LoginAttempt.php
│   │   │   │   └── PasswordReset.php
│   │   │   ├── Enums/
│   │   │   │   ├── RoleType.php
│   │   │   │   └── PermissionType.php
│   │   │   ├── Events/
│   │   │   │   ├── UserLoggedIn.php
│   │   │   │   ├── UserLoggedOut.php
│   │   │   │   ├── LoginFailed.php
│   │   │   │   ├── PasswordChanged.php
│   │   │   │   ├── RoleAssigned.php
│   │   │   │   └── PermissionGranted.php
│   │   │   ├── Guards/
│   │   │   │   ├── JuryGuard.php
│   │   │   │   └── ApplicantGuard.php
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── LoginController.php
│   │   │   │   │   ├── RegisterController.php
│   │   │   │   │   ├── ForgotPasswordController.php
│   │   │   │   │   ├── ResetPasswordController.php
│   │   │   │   │   ├── RoleController.php
│   │   │   │   │   └── PermissionController.php
│   │   │   │   ├── Middleware/
│   │   │   │   │   ├── CheckRole.php
│   │   │   │   │   ├── CheckPermission.php
│   │   │   │   │   ├── TwoFactorAuth.php
│   │   │   │   │   └── IpWhitelist.php
│   │   │   │   ├── Requests/
│   │   │   │   │   ├── LoginRequest.php
│   │   │   │   │   ├── RegisterRequest.php
│   │   │   │   │   ├── ForgotPasswordRequest.php
│   │   │   │   │   └── ResetPasswordRequest.php
│   │   │   │   └── Resources/
│   │   │   │       ├── RoleResource.php
│   │   │   │       └── PermissionResource.php
│   │   │   ├── Listeners/
│   │   │   │   ├── LogSuccessfulLogin.php
│   │   │   │   ├── LogFailedLogin.php
│   │   │   │   └── SendPasswordResetNotification.php
│   │   │   ├── Policies/
│   │   │   │   ├── RolePolicy.php
│   │   │   │   └── PermissionPolicy.php
│   │   │   ├── Providers/
│   │   │   │   └── AuthServiceProvider.php
│   │   │   ├── Repositories/
│   │   │   │   ├── Contracts/
│   │   │   │   │   ├── RoleRepositoryInterface.php
│   │   │   │   │   └── PermissionRepositoryInterface.php
│   │   │   │   └── Eloquent/
│   │   │   │       ├── RoleRepository.php
│   │   │   │       └── PermissionRepository.php
│   │   │   └── Services/
│   │   │       ├── Contracts/
│   │   │       │   └── AuthServiceInterface.php
│   │   │       ├── AuthService.php
│   │   │       ├── RoleService.php
│   │   │       ├── PermissionService.php
│   │   │       ├── SessionService.php
│   │   │       └── TwoFactorService.php
│   │   ├── config/
│   │   │   └── config.php
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   │   ├── 2024_01_02_000001_create_roles_table.php
│   │   │   │   ├── 2024_01_02_000002_create_permissions_table.php
│   │   │   │   ├── 2024_01_02_000003_create_role_user_table.php
│   │   │   │   ├── 2024_01_02_000004_create_permission_role_table.php
│   │   │   │   ├── 2024_01_02_000005_create_user_sessions_table.php
│   │   │   │   ├── 2024_01_02_000006_create_login_attempts_table.php
│   │   │   │   └── 2024_01_02_000007_create_password_resets_table.php
│   │   │   ├── seeders/
│   │   │   │   ├── AuthDatabaseSeeder.php
│   │   │   │   ├── RoleSeeder.php
│   │   │   │   └── PermissionSeeder.php
│   │   │   └── factories/
│   │   ├── routes/
│   │   │   ├── api.php
│   │   │   └── web.php
│   │   ├── resources/
│   │   │   ├── views/
│   │   │   │   ├── login.blade.php
│   │   │   │   ├── register.blade.php
│   │   │   │   ├── forgot-password.blade.php
│   │   │   │   ├── reset-password.blade.php
│   │   │   │   ├── roles/
│   │   │   │   │   ├── index.blade.php
│   │   │   │   │   ├── create.blade.php
│   │   │   │   │   └── edit.blade.php
│   │   │   │   └── permissions/
│   │   │   │       └── index.blade.php
│   │   │   └── lang/
│   │   │       ├── en/
│   │   │       │   └── auth.php
│   │   │       └── es/
│   │   │           └── auth.php
│   │   ├── tests/
│   │   │   ├── Unit/
│   │   │   │   ├── RoleServiceTest.php
│   │   │   │   └── PermissionServiceTest.php
│   │   │   └── Feature/
│   │   │       ├── LoginTest.php
│   │   │       ├── RegisterTest.php
│   │   │       ├── PasswordResetTest.php
│   │   │       └── RolePermissionTest.php
│   │   ├── composer.json
│   │   └── module.json
│   │
│   ├── User/                           # ═══════════════════════════════════
│   │   │                               # MÓDULO USER
│   │   ├── app/
│   │   │   ├── Entities/
│   │   │   │   ├── User.php
│   │   │   │   ├── UserProfile.php
│   │   │   │   ├── UserPreference.php
│   │   │   │   └── UserOrganizationUnit.php
│   │   │   ├── Enums/
│   │   │   │   ├── Gender.php
│   │   │   │   ├── Language.php
│   │   │   │   └── Theme.php
│   │   │   ├── Events/
│   │   │   │   ├── UserCreated.php
│   │   │   │   ├── UserUpdated.php
│   │   │   │   ├── UserDeleted.php
│   │   │   │   ├── UserActivated.php
│   │   │   │   ├── UserDeactivated.php
│   │   │   │   └── UserOrganizationChanged.php
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── UserController.php
│   │   │   │   │   ├── ProfileController.php
│   │   │   │   │   └── PreferenceController.php
│   │   │   │   ├── Requests/
│   │   │   │   │   ├── StoreUserRequest.php
│   │   │   │   │   ├── UpdateUserRequest.php
│   │   │   │   │   ├── UpdateProfileRequest.php
│   │   │   │   │   └── UpdatePreferenceRequest.php
│   │   │   │   └── Resources/
│   │   │   │       ├── UserResource.php
│   │   │   │       ├── UserCollection.php
│   │   │   │       ├── ProfileResource.php
│   │   │   │       └── PreferenceResource.php
│   │   │   ├── Listeners/
│   │   │   │   ├── CreateUserProfile.php
│   │   │   │   ├── CreateUserPreferences.php
│   │   │   │   └── SendWelcomeNotification.php
│   │   │   ├── Policies/
│   │   │   │   └── UserPolicy.php
│   │   │   ├── Providers/
│   │   │   │   └── UserServiceProvider.php
│   │   │   ├── Repositories/
│   │   │   │   ├── Contracts/
│   │   │   │   │   └── UserRepositoryInterface.php
│   │   │   │   └── Eloquent/
│   │   │   │       └── UserRepository.php
│   │   │   └── Services/
│   │   │       ├── Contracts/
│   │   │       │   └── UserServiceInterface.php
│   │   │       ├── UserService.php
│   │   │       ├── ProfileService.php
│   │   │       ├── PreferenceService.php
│   │   │       └── AssignmentService.php
│   │   ├── config/
│   │   │   └── config.php
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   │   ├── 2024_01_03_000001_create_users_table.php
│   │   │   │   ├── 2024_01_03_000002_create_user_profiles_table.php
│   │   │   │   ├── 2024_01_03_000003_create_user_preferences_table.php
│   │   │   │   └── 2024_01_03_000004_create_user_organization_units_table.php
│   │   │   ├── seeders/
│   │   │   │   ├── UserDatabaseSeeder.php
│   │   │   │   └── UserSeeder.php
│   │   │   └── factories/
│   │   │       ├── UserFactory.php
│   │   │       ├── UserProfileFactory.php
│   │   │       └── UserPreferenceFactory.php
│   │   ├── routes/
│   │   │   ├── api.php
│   │   │   └── web.php
│   │   ├── resources/
│   │   │   ├── views/
│   │   │   │   ├── index.blade.php
│   │   │   │   ├── show.blade.php
│   │   │   │   ├── create.blade.php
│   │   │   │   ├── edit.blade.php
│   │   │   │   ├── profile/
│   │   │   │   │   ├── show.blade.php
│   │   │   │   │   └── edit.blade.php
│   │   │   │   └── preferences/
│   │   │   │       └── edit.blade.php
│   │   │   └── lang/
│   │   │       ├── en/
│   │   │       │   └── user.php
│   │   │       └── es/
│   │   │           └── user.php
│   │   ├── tests/
│   │   │   ├── Unit/
│   │   │   │   ├── UserServiceTest.php
│   │   │   │   └── ProfileServiceTest.php
│   │   │   └── Feature/
│   │   │       ├── UserManagementTest.php
│   │   │       ├── ProfileTest.php
│   │   │       └── PreferenceTest.php
│   │   ├── composer.json
│   │   └── module.json
│   │
│   ├── Organization/                   # ═══════════════════════════════════
│   │   │                               # MÓDULO ORGANIZATION
│   │   ├── app/
│   │   │   ├── Entities/
│   │   │   │   ├── OrganizationalUnit.php
│   │   │   │   ├── OrganizationalUnitClosure.php
│   │   │   │   └── OrganizationalUnitHistory.php
│   │   │   ├── Enums/
│   │   │   │   └── OrganizationalUnitType.php
│   │   │   ├── Events/
│   │   │   │   ├── OrganizationalUnitCreated.php
│   │   │   │   ├── OrganizationalUnitUpdated.php
│   │   │   │   ├── OrganizationalUnitMoved.php
│   │   │   │   └── OrganizationalUnitDeleted.php
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── OrganizationalUnitController.php
│   │   │   │   │   └── HierarchyController.php
│   │   │   │   ├── Requests/
│   │   │   │   │   ├── StoreOrganizationalUnitRequest.php
│   │   │   │   │   ├── UpdateOrganizationalUnitRequest.php
│   │   │   │   │   └── MoveOrganizationalUnitRequest.php
│   │   │   │   └── Resources/
│   │   │   │       ├── OrganizationalUnitResource.php
│   │   │   │       └── OrganizationalUnitTreeResource.php
│   │   │   ├── Listeners/
│   │   │   │   ├── UpdateClosureTable.php
│   │   │   │   └── LogOrganizationalChange.php
│   │   │   ├── Observers/
│   │   │   │   └── OrganizationalUnitObserver.php
│   │   │   ├── Policies/
│   │   │   │   └── OrganizationalUnitPolicy.php
│   │   │   ├── Providers/
│   │   │   │   └── OrganizationServiceProvider.php
│   │   │   ├── Repositories/
│   │   │   │   ├── Contracts/
│   │   │   │   │   └── OrganizationalUnitRepositoryInterface.php
│   │   │   │   └── Eloquent/
│   │   │   │       └── OrganizationalUnitRepository.php
│   │   │   └── Services/
│   │   │       ├── Contracts/
│   │   │       │   └── OrganizationServiceInterface.php
│   │   │       ├── OrganizationService.php
│   │   │       ├── HierarchyService.php
│   │   │       └── TreeService.php
│   │   ├── config/
│   │   │   └── config.php
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   │   ├── 2024_01_04_000001_create_organizational_units_table.php
│   │   │   │   ├── 2024_01_04_000002_create_organizational_unit_closure_table.php
│   │   │   │   └── 2024_01_04_000003_create_organizational_unit_history_table.php
│   │   │   ├── seeders/
│   │   │   │   ├── OrganizationDatabaseSeeder.php
│   │   │   │   └── OrganizationalUnitSeeder.php
│   │   │   └── factories/
│   │   │       └── OrganizationalUnitFactory.php
│   │   ├── routes/
│   │   │   ├── api.php
│   │   │   └── web.php
│   │   ├── resources/
│   │   │   ├── views/
│   │   │   │   ├── index.blade.php
│   │   │   │   ├── show.blade.php
│   │   │   │   ├── create.blade.php
│   │   │   │   ├── edit.blade.php
│   │   │   │   └── tree.blade.php
│   │   │   └── lang/
│   │   │       ├── en/
│   │   │       │   └── organization.php
│   │   │       └── es/
│   │   │           └── organization.php
│   │   ├── tests/
│   │   │   ├── Unit/
│   │   │   │   ├── HierarchyServiceTest.php
│   │   │   │   └── TreeServiceTest.php
│   │   │   └── Feature/
│   │   │       ├── OrganizationalUnitTest.php
│   │   │       └── HierarchyTest.php
│   │   ├── composer.json
│   │   └── module.json
│   │
│   ├── JobPosting/                     # ═══════════════════════════════════
│   │   │                               # MÓDULO JOBPOSTING
│   │   ├── app/
│   │   │   ├── Entities/
│   │   │   │   ├── JobPosting.php
│   │   │   │   ├── ProcessPhase.php
│   │   │   │   ├── JobPostingSchedule.php
│   │   │   │   ├── JobPostingPhaseStatus.php
│   │   │   │   └── JobPostingHistory.php
│   │   │   ├── Enums/
│   │   │   │   ├── JobPostingStatus.php
│   │   │   │   └── PhaseStatus.php
│   │   │   ├── Events/
│   │   │   │   ├── JobPostingCreated.php
│   │   │   │   ├── JobPostingPublished.php
│   │   │   │   ├── JobPostingStarted.php
│   │   │   │   ├── JobPostingFinalized.php
│   │   │   │   ├── JobPostingCancelled.php
│   │   │   │   ├── PhaseStarted.php
│   │   │   │   ├── PhaseCompleted.php
│   │   │   │   └── ScheduleDelayed.php
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── JobPostingController.php
│   │   │   │   │   ├── ScheduleController.php
│   │   │   │   │   ├── PhaseController.php
│   │   │   │   │   └── WorkflowController.php
│   │   │   │   ├── Requests/
│   │   │   │   │   ├── StoreJobPostingRequest.php
│   │   │   │   │   ├── UpdateJobPostingRequest.php
│   │   │   │   │   ├── PublishJobPostingRequest.php
│   │   │   │   │   └── CancelJobPostingRequest.php
│   │   │   │   └── Resources/
│   │   │   │       ├── JobPostingResource.php
│   │   │   │       ├── JobPostingCollection.php
│   │   │   │       ├── ScheduleResource.php
│   │   │   │       └── PhaseResource.php
│   │   │   ├── Listeners/
│   │   │   │   ├── NotifyJobPostingPublished.php
│   │   │   │   ├── InitializePhases.php
│   │   │   │   └── LogJobPostingChange.php
│   │   │   ├── Observers/
│   │   │   │   └── JobPostingObserver.php
│   │   │   ├── Policies/
│   │   │   │   └── JobPostingPolicy.php
│   │   │   ├── Providers/
│   │   │   │   └── JobPostingServiceProvider.php
│   │   │   ├── Repositories/
│   │   │   │   ├── Contracts/
│   │   │   │   │   └── JobPostingRepositoryInterface.php
│   │   │   │   └── Eloquent/
│   │   │   │       └── JobPostingRepository.php
│   │   │   ├── Services/
│   │   │   │   ├── Contracts/
│   │   │   │   │   └── JobPostingServiceInterface.php
│   │   │   │   ├── JobPostingService.php
│   │   │   │   ├── ScheduleService.php
│   │   │   │   ├── PhaseService.php
│   │   │   │   ├── WorkflowService.php
│   │   │   │   └── PublicationService.php
│   │   │   └── StateMachines/          # ← State Machine para flujo de estados
│   │   │       ├── JobPostingStateMachine.php
│   │   │       └── Transitions/
│   │   │           ├── PublishTransition.php
│   │   │           ├── StartTransition.php
│   │   │           ├── FinalizeTransition.php
│   │   │           └── CancelTransition.php
│   │   ├── config/
│   │   │   └── config.php
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   │   ├── 2024_01_05_000001_create_job_postings_table.php
│   │   │   │   ├── 2024_01_05_000002_create_process_phases_table.php
│   │   │   │   ├── 2024_01_05_000003_create_job_posting_schedules_table.php
│   │   │   │   ├── 2024_01_05_000004_create_job_posting_phase_statuses_table.php
│   │   │   │   └── 2024_01_05_000005_create_job_posting_history_table.php
│   │   │   ├── seeders/
│   │   │   │   ├── JobPostingDatabaseSeeder.php
│   │   │   │   └── ProcessPhaseSeeder.php
│   │   │   └── factories/
│   │   │       ├── JobPostingFactory.php
│   │   │       └── JobPostingScheduleFactory.php
│   │   ├── routes/
│   │   │   ├── api.php
│   │   │   └── web.php
│   │   ├── resources/
│   │   │   ├── views/
│   │   │   │   ├── index.blade.php
│   │   │   │   ├── show.blade.php
│   │   │   │   ├── create.blade.php
│   │   │   │   ├── edit.blade.php
│   │   │   │   ├── schedule/
│   │   │   │   │   ├── index.blade.php
│   │   │   │   │   └── edit.blade.php
│   │   │   │   └── workflow/
│   │   │   │       └── show.blade.php
│   │   │   └── lang/
│   │   │       ├── en/
│   │   │       │   └── jobposting.php
│   │   │       └── es/
│   │   │           └── jobposting.php
│   │   ├── tests/
│   │   │   ├── Unit/
│   │   │   │   ├── JobPostingServiceTest.php
│   │   │   │   ├── WorkflowServiceTest.php
│   │   │   │   └── StateMachineTest.php
│   │   │   └── Feature/
│   │   │       ├── JobPostingManagementTest.php
│   │   │       ├── ScheduleTest.php
│   │   │       ├── WorkflowTest.php
│   │   │       └── PublicationTest.php
│   │   ├── composer.json
│   │   └── module.json
│   │
│   └── JobProfile/                     # ═══════════════════════════════════
│       │                               # MÓDULO JOBPROFILE
│       ├── app/
│       │   ├── Entities/
│       │   │   ├── JobProfileRequest.php
│       │   │   ├── PositionCode.php
│       │   │   ├── EvaluationCriterion.php
│       │   │   ├── JobProfileVacancy.php
│       │   │   └── JobProfileHistory.php
│       │   ├── Enums/
│       │   │   ├── JobProfileStatus.php
│       │   │   ├── EducationLevel.php
│       │   │   ├── WorkRegime.php
│       │   │   └── VacancyStatus.php
│       │   ├── Events/
│       │   │   ├── ProfileRequested.php
│       │   │   ├── ProfileInReview.php
│       │   │   ├── ProfileModificationRequested.php
│       │   │   ├── ProfileApproved.php
│       │   │   ├── ProfileRejected.php
│       │   │   ├── VacanciesGenerated.php
│       │   │   └── CriteriaUpdated.php
│       │   ├── Http/
│       │   │   ├── Controllers/
│       │   │   │   ├── JobProfileController.php
│       │   │   │   ├── PositionCodeController.php
│       │   │   │   ├── CriterionController.php
│       │   │   │   ├── VacancyController.php
│       │   │   │   └── ReviewController.php
│       │   │   ├── Requests/
│       │   │   │   ├── StoreJobProfileRequest.php
│       │   │   │   ├── UpdateJobProfileRequest.php
│       │   │   │   ├── ReviewJobProfileRequest.php
│       │   │   │   ├── StorePositionCodeRequest.php
│       │   │   │   └── StoreCriterionRequest.php
│       │   │   └── Resources/
│       │   │       ├── JobProfileResource.php
│       │   │       ├── JobProfileCollection.php
│       │   │       ├── PositionCodeResource.php
│       │   │       ├── CriterionResource.php
│       │   │       └── VacancyResource.php
│       │   ├── Listeners/
│       │   │   ├── GenerateVacancies.php
│       │   │   ├── NotifyProfileApproved.php
│       │   │   └── LogProfileChange.php
│       │   ├── Observers/
│       │   │   └── JobProfileObserver.php
│       │   ├── Policies/
│       │   │   ├── JobProfilePolicy.php
│       │   │   └── PositionCodePolicy.php
│       │   ├── Providers/
│       │   │   └── JobProfileServiceProvider.php
│       │   ├── Repositories/
│       │   │   ├── Contracts/
│       │   │   │   ├── JobProfileRepositoryInterface.php
│       │   │   │   └── PositionCodeRepositoryInterface.php
│       │   │   └── Eloquent/
│       │   │       ├── JobProfileRepository.php
│       │   │       └── PositionCodeRepository.php
│       │   ├── Services/
│       │   │   ├── Contracts/
│       │   │   │   └── JobProfileServiceInterface.php
│       │   │   ├── JobProfileService.php
│       │   │   ├── PositionCodeService.php
│       │   │   ├── CriterionService.php
│       │   │   ├── VacancyService.php
│       │   │   └── ReviewService.php
│       │   └── StateMachines/
│       │       ├── JobProfileStateMachine.php
│       │       └── Transitions/
│       │           ├── SubmitForReviewTransition.php
│       │           ├── RequestModificationTransition.php
│       │           ├── ApproveTransition.php
│       │           └── RejectTransition.php
│       ├── config/
│       │   └── config.php
│       ├── database/
│       │   ├── migrations/
│       │   │   ├── 2024_01_06_000001_create_job_profile_requests_table.php
│       │   │   ├── 2024_01_06_000002_create_position_codes_table.php
│       │   │   ├── 2024_01_06_000003_create_evaluation_criteria_table.php
│       │   │   ├── 2024_01_06_000004_create_job_profile_vacancies_table.php
│       │   │   └── 2024_01_06_000005_create_job_profile_history_table.php
│       │   ├── seeders/
│       │   │   ├── JobProfileDatabaseSeeder.php
│       │   │   ├── PositionCodeSeeder.php
│       │   │   └── EvaluationCriterionSeeder.php
│       │   └── factories/
│       │       ├── JobProfileRequestFactory.php
│       │       ├── PositionCodeFactory.php
│       │       └── JobProfileVacancyFactory.php
│       ├── routes/
│       │   ├── api.php
│       │   └── web.php
│       ├── resources/
│       │   ├── views/
│       │   │   ├── index.blade.php
│       │   │   ├── show.blade.php
│       │   │   ├── create.blade.php
│       │   │   ├── edit.blade.php
│       │   │   ├── review/
│       │   │   │   ├── index.blade.php
│       │   │   │   └── show.blade.php
│       │   │   ├── positions/
│       │   │   │   ├── index.blade.php
│       │   │   │   ├── create.blade.php
│       │   │   │   └── edit.blade.php
│       │   │   └── criteria/
│       │   │       ├── index.blade.php
│       │   │       └── edit.blade.php
│       │   └── lang/
│       │       ├── en/
│       │       │   └── jobprofile.php
│       │       └── es/
│       │           └── jobprofile.php
│       ├── tests/
│       │   ├── Unit/
│       │   │   ├── JobProfileServiceTest.php
│       │   │   ├── VacancyServiceTest.php
│       │   │   ├── CalculationTest.php
│       │   │   └── StateMachineTest.php
│       │   └── Feature/
│       │       ├── JobProfileManagementTest.php
│       │       ├── ReviewProcessTest.php
│       │       ├── VacancyGenerationTest.php
│       │       └── CriterionTest.php
│       ├── composer.json
│       └── module.json
│
├── .editorconfig
├── .env.example
├── .gitattributes
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── phpunit.xml
├── README.md
└── vite.config.js
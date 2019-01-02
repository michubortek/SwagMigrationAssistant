import { Component, State } from 'src/core/shopware';
import CriteriaFactory from 'src/core/factory/criteria.factory';
import template from './swag-migration-wizard-page-select-profile.html.twig';

Component.register('swag-migration-wizard-page-select-profile', {
    template,

    created() {
        this.createdComponent();
    },

    data() {
        return {
            isLoading: false,
            selection: {
                profile: null,
                gateway: null
            },
            profiles: [],
            gateways: []
        };
    },

    computed: {
        profileStore() {
            return State.getStore('swag_migration_profile');
        },

        generalSettingStore() {
            return State.getStore('swag_migration_general_setting');
        }
    },

    methods: {
        createdComponent() {
            this.setIsLoading(true);
            this.emitProfileSelectionValidation(false);

            this.profileStore.getList({
                aggregations: {
                    profileAgg: {
                        value: { field: 'profile' }
                    }
                }
            }).then((profiles) => {
                this.profiles = profiles.aggregations.profileAgg;

                const params = {
                    offset: 0,
                    limit: 1
                };
                this.generalSettingStore.getList(params).then((response) => {
                    if (!response || response.items[0].selectedProfileId === null) {
                        this.setIsLoading(false);
                        return;
                    }

                    this.profileStore.getByIdAsync(response.items[0].selectedProfileId).then((profileResponse) => {
                        if (profileResponse.id === null) {
                            this.setIsLoading(false);
                            return;
                        }

                        this.selection.profile = profileResponse.profile;
                        this.onSelectProfile().then(() => {
                            this.selection.gateway = profileResponse.gateway;
                            this.onSelectGateway().then(() => {
                                this.emitProfileSelectionValidation(true);
                                this.setIsLoading(false);
                            });
                        });
                    });
                });
            });
        },

        setIsLoading(value) {
            this.$emit('onIsLoadingChanged', value);
        },

        onSelectProfile() {
            return new Promise((resolve) => {
                this.emitProfileSelectionValidation(false);
                this.gateways = null;

                if (this.selection.profile !== null) {
                    const criteria = CriteriaFactory.multi(
                        'AND',
                        CriteriaFactory.equals('profile', this.selection.profile),
                        // Todo: Remove if not playground
                        CriteriaFactory.equals('gateway', 'api')
                    );

                    this.profileStore.getList({
                        criteria: criteria,
                        aggregations: {
                            gatewayAgg: {
                                value: { field: 'gateway' }
                            }
                        }
                    }).then((profiles) => {
                        this.gateways = profiles.aggregations.gatewayAgg;
                        this.selection.gateway = null;
                        this.emitProfileSelectionValidation(false);
                        resolve();
                    });
                }
            });
        },

        onSelectGateway() {
            return new Promise((resolve) => {
                this.emitProfileSelectionValidation(false);

                const criteria = CriteriaFactory.multi(
                    'AND',
                    CriteriaFactory.equals('profile', this.selection.profile),
                    CriteriaFactory.equals('gateway', this.selection.gateway)
                );

                this.profileStore.getList({
                    criteria: criteria
                }).then((profile) => {
                    if (profile.total !== 0) {
                        this.$emit('onProfileSelected', profile.items[0]);
                        this.emitProfileSelectionValidation(true);
                    }
                    resolve();
                });
            });
        },

        emitProfileSelectionValidation(validation) {
            this.$emit('onProfileSelectionValidationChanged', validation);
        }
    }
});
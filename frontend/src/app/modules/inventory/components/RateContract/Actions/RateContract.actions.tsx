import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import RateContractFormController from '../Form/RateContractForm.controller'
import RateContractViewController from '../View/RateContractView.controller'

export const RateContractAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Rate Contract',
      action: 'create',
      link: {to: ''},
      permission: 'auth:rateContract:create',
      component: RateContractFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Rate Contract',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:rateContract:edit',
      component: RateContractFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Rate Contract',
      action: 'view',
      link: {to: ''},
      permission: 'auth:rateContract:view',
      component: RateContractViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Rate Contract',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:rateContract:delete',
      component: '',
      className: 'grid-view-action',
    },
  },
  LIST_ITEM_ACTION: [
    {
      type: 'item',
      title: 'View',
      action: 'active',
      link: {to: ''},
      permission: 'auth:rateContract:view',
      component: ViewAction,
      className: '',
      modalId: 'view',
      icon: 'information-4',
    },
    {
      type: 'item',
      title: 'Edit',
      action: 'inactive',
      link: {to: ''},
      permission: 'auth:rateContract:edit',
      component: EditAction,
      className: '',
      modalId: 'edit',
      icon: 'pencil',
    },
    {
      type: 'item',
      title: 'Delete',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:rateContract:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:rateContract:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:rateContract:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}

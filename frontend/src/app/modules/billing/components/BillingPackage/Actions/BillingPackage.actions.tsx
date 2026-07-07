import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import BillingPackageFormController from '../Form/BillingPackageForm.controller'
import BillingPackageViewController from '../View/BillingPackageView.controller'

export const BillingPackageAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Billing Package',
      action: 'create',
      link: {to: ''},
      permission: 'auth:billing-package:create',
      component: BillingPackageFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Billing Package',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:billing-package:edit',
      component: BillingPackageFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Billing Package',
      action: 'view',
      link: {to: ''},
      permission: 'auth:billing-package:view',
      component: BillingPackageViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Billing Package',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:billing-package:delete',
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
      permission: 'auth:billing-package:view',
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
      permission: 'auth:billing-package:edit',
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
      permission: 'auth:billing-package:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:billing-package:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:billing-package:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}

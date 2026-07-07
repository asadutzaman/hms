import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import InsuranceCompanyFormController from '../Form/InsuranceCompanyForm.controller'
import InsuranceCompanyViewController from '../View/InsuranceCompanyView.controller'

export const InsuranceCompanyAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Insurance Company',
      action: 'create',
      link: {to: ''},
      permission: 'auth:insurance-company:create',
      component: InsuranceCompanyFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Insurance Company',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:insurance-company:edit',
      component: InsuranceCompanyFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Insurance Company',
      action: 'view',
      link: {to: ''},
      permission: 'auth:insurance-company:view',
      component: InsuranceCompanyViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Insurance Company',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:insurance-company:delete',
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
      permission: 'auth:insurance-company:view',
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
      permission: 'auth:insurance-company:edit',
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
      permission: 'auth:insurance-company:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:insurance-company:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:insurance-company:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}

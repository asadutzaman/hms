import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import EmployeeFormController from '../Form/EmployeeForm.controller'
import EmployeeViewController from '../View/EmployeeView.controller'

export const EmployeeAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Employee',
      action: 'create',
      link: {to: ''},
      permission: 'auth:employee:create',
      component: EmployeeFormController,
      className: 'grid-view-action',
    },
    VIEW: {
      type: 'item',
      title: 'View Employee',
      action: 'view',
      link: {to: ''},
      permission: 'auth:employee:view',
      component: EmployeeViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Employee',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:employee:edit',
      component: EmployeeFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Employee',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:employee:delete',
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
      permission: 'auth:employee:view',
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
      permission: 'auth:employee:edit',
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
      permission: 'auth:employee:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:employee:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Mark as Active',
        action: 'active',
        link: {to: ''},
        component: '',
        className: 'grid-view-action',
      },
      {
        type: 'item',
        title: 'Mark as Inactive',
        action: 'inactive',
        link: {to: ''},
        component: '',
        className: 'grid-view-action',
      },
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:employee:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}

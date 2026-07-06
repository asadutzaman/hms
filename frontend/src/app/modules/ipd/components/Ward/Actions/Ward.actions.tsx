import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import WardFormController from '../Form/WardForm.controller'
import WardViewController from '../View/WardView.controller'

export const WardAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Ward',
      action: 'create',
      link: {to: ''},
      permission: 'auth:ward:create',
      component: WardFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Ward',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:ward:edit',
      component: WardFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Ward',
      action: 'view',
      link: {to: ''},
      permission: 'auth:ward:view',
      component: WardViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Ward',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:ward:delete',
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
      permission: 'auth:ward:view',
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
      permission: 'auth:ward:edit',
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
      permission: 'auth:ward:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:ward:multiSelect',
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
        permission: 'auth:ward:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}

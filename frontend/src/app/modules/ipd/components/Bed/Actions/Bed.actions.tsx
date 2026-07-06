import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import BedFormController from '../Form/BedForm.controller'
import BedViewController from '../View/BedView.controller'

export const BedAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Bed',
      action: 'create',
      link: {to: ''},
      permission: 'auth:bed:create',
      component: BedFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Bed',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:bed:edit',
      component: BedFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Bed',
      action: 'view',
      link: {to: ''},
      permission: 'auth:bed:view',
      component: BedViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Bed',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:bed:delete',
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
      permission: 'auth:bed:view',
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
      permission: 'auth:bed:edit',
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
      permission: 'auth:bed:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:bed:multiSelect',
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
        permission: 'auth:bed:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}

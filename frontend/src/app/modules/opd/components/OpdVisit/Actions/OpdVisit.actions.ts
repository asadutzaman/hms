import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import OpdVisitFormController from '../Form/OpdVisitForm.controller'
import OpdVisitViewController from '../View/OpdVisitView.controller'

export const OpdVisitAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New OpdVisit',
      action: 'create',
      link: {to: ''},
      permission: 'auth:OpdVisit:create',
      component: OpdVisitFormController,
      className: 'grid-view-action',
    },
    VIEW: {
      type: 'item',
      title: 'View OpdVisit',
      action: 'view',
      link: {to: ''},
      permission: 'auth:OpdVisit:view',
      component: OpdVisitViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    EDIT: {
      type: 'item',
      title: 'Edit OpdVisit',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:OpdVisit:edit',
      component: OpdVisitFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    DELETE: {
      type: 'item',
      title: 'Delete OpdVisit',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:OpdVisit:delete',
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
      permission: 'auth:OpdVisit:view',
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
      permission: 'auth:OpdVisit:edit',
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
      permission: 'auth:OpdVisit:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:OpdVisit:multiSelect',
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
        permission: 'auth:OpdVisit:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AppointmentFormController from '../Form/AppointmentForm.controller'
import AppointmentViewController from '../View/AppointmentView.controller'

export const AppointmentAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Appointment',
      action: 'create',
      link: {to: ''},
      permission: 'auth:appointment:create',
      component: AppointmentFormController,
      className: 'grid-view-action',
    },
    VIEW: {
      type: 'item',
      title: 'View Appointment',
      action: 'view',
      link: {to: ''},
      permission: 'auth:appointment:view',
      component: AppointmentViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Appointment',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:appointment:edit',
      component: AppointmentFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Appointment',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:appointment:delete',
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
      permission: 'auth:appointment:view',
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
      permission: 'auth:appointment:edit',
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
      permission: 'auth:appointment:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:appointment:multiSelect',
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
        permission: 'auth:appointment:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
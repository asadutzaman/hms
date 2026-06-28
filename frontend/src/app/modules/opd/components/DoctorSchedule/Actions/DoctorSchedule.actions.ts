import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import DoctorScheduleFormController from '../Form/DoctorScheduleForm.controller'
import DoctorScheduleViewController from '../View/DoctorScheduleView.controller'

export const DoctorScheduleAction: any = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Doctor Schedule',
      action: 'create',
      link: {to: ''},
      permission: 'auth:doctor-schedule:create',
      component: DoctorScheduleFormController,
      className: 'grid-view-action',
    },
    VIEW: {
      type: 'item',
      title: 'View Doctor Schedule',
      action: 'view',
      link: {to: ''},
      permission: 'auth:doctor-schedule:view',
      component: DoctorScheduleViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Doctor Schedule',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:doctor-schedule:edit',
      component: DoctorScheduleFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Doctor Schedule',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:doctor-schedule:delete',
      component: '',
      className: 'grid-view-action',
    },
  },
  LIST_ITEM_ACTION: [
    {
      type: 'item',
      title: 'View',
      action: 'view',
      link: {to: ''},
      permission: 'auth:doctor-schedule:view',
      component: ViewAction,
      className: '',
      modalId: 'view',
      icon: 'information-4',
    },
    {
      type: 'item',
      title: 'Edit',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:doctor-schedule:edit',
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
      permission: 'auth:doctor-schedule:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:doctor-schedule:multiSelect',
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
        permission: 'auth:doctor-schedule:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}

export default DoctorScheduleAction

import SeatController from './SeatController'
import BookingController from './BookingController'
import ExportController from './ExportController'
import Settings from './Settings'

const Controllers = {
    SeatController: Object.assign(SeatController, SeatController),
    BookingController: Object.assign(BookingController, BookingController),
    ExportController: Object.assign(ExportController, ExportController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers
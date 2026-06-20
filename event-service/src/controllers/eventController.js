const Category = require('../models/Category');
const Event = require('../models/Event');
const { imageUrl } = require('../utils/fileHandler');
const { successResponse, errorResponse } = require('../utils/response');

function mapEvent(event) {
  return event ? { ...event, image_url: imageUrl(event.image_path) } : null;
}

async function categories(req, res, next) {
  try {
    return res.json({ success: true, data: await Category.findAll() });
  } catch (error) {
    return next(error);
  }
}

async function index(req, res, next) {
  try {
    const page = Number(req.query.page || 1);
    const limit = Number(req.query.limit || 10);
    const { rows, total } = await Event.findAll({ ...req.query, limit, offset: (page - 1) * limit });
    return res.json({
      success: true,
      data: {
        events: rows.map(mapEvent),
        pagination: {
          current_page: page,
          total_pages: Math.ceil(total / limit),
          total_items: total,
          items_per_page: limit,
        },
      },
    });
  } catch (error) {
    return next(error);
  }
}

async function show(req, res, next) {
  try {
    const event = await Event.findById(req.params.id);
    if (!event) return errorResponse(res, 404, 'Event not found');
    return res.json({ success: true, data: mapEvent(event) });
  } catch (error) {
    return next(error);
  }
}

async function store(req, res, next) {
  try {
    const event = await Event.createEvent({ ...req.body, image_path: req.file?.filename || null, created_by: req.user.id });
    return successResponse(res, 201, 'Event created successfully', mapEvent(event));
  } catch (error) {
    return next(error);
  }
}

async function update(req, res, next) {
  try {
    const event = await Event.updateEvent(req.params.id, { ...req.body, image_path: req.file?.filename });
    if (!event) return errorResponse(res, 404, 'Event not found');
    return successResponse(res, 200, 'Event updated successfully', mapEvent(event));
  } catch (error) {
    return next(error);
  }
}

async function destroy(req, res, next) {
  try {
    const event = await Event.deleteEvent(req.params.id);
    if (!event) return errorResponse(res, 404, 'Event not found');
    return successResponse(res, 200, 'Event deleted successfully');
  } catch (error) {
    return next(error);
  }
}

async function checkAvailability(req, res, next) {
  try {
    const event = await Event.findById(req.body.event_id);
    if (!event) return errorResponse(res, 404, 'Event not found');
    const available = event.available_tickets >= Number(req.body.quantity || 0);
    if (!available) return errorResponse(res, 400, 'Not enough tickets available', { available: false, available_tickets: event.available_tickets });
    return res.json({ success: true, data: { available: true, event: mapEvent(event) } });
  } catch (error) {
    return next(error);
  }
}

async function reduceQuota(req, res, next) {
  try {
    const event = await Event.reduceQuota(req.body.event_id, Number(req.body.quantity));
    return successResponse(res, 200, 'Quota reduced successfully', { available_tickets: event?.available_tickets });
  } catch (error) {
    return next(error);
  }
}

module.exports = { categories, index, show, store, update, destroy, checkAvailability, reduceQuota };

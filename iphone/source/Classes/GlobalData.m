//
//  GlobalData.m
//  Redigone


#import "GlobalData.h"

#define DEV false

#define API_URL @"http://api.redigone.com"
#define SECURE_API_URL @"https://api.redigone.com"

#define API_URL_DEV @"http://api.dev.redigone.com"
#define SECURE_API_URL_DEV @"http://api.dev.redigone.com"

//#define API_URL_DEV @"http://api.rig.com"
//#define SECURE_API_URL_DEV @"https://api.rig.com"

#define API_KEY @"f3c14382068e9b476077ce6f885c77f2"

#define API_ADAPTIVE_RESIZE @"290x265"
#define ADAPTIVE_SIZE_X 290
#define ADAPTIVE_SIZE_Y 265

#define IMAGE_POSITION_X 160
#define IMAGE_POSITION_Y 185

#define IMG_EXTENSION @"jpg"

#define DEFAULT_RADIUS 18
#define MAX_POINTS 20

@implementation GlobalData

@synthesize selectedPoints;
@synthesize maxPoints;
@synthesize default_radius;
@synthesize imageViewUpperLeft;
@synthesize imageViewLowerRight;
@synthesize nextPointName;
@synthesize currentPointView;
@synthesize image;
@synthesize ratio;
@synthesize original_width;
@synthesize original_height;
@synthesize image_url;
@synthesize thumbImages;
@synthesize adaptiveResize;
@synthesize extension;
@synthesize adaptiveSizeX;
@synthesize adaptiveSizeY;
@synthesize imagePositionX;
@synthesize imagePositionY;
@synthesize unrecoverableError;
@synthesize statusCode;
@synthesize saveStatusCode;

//XML Vars
@synthesize queue_id;
@synthesize queue_th;
@synthesize queue_name;
@synthesize original_name;
@synthesize original_thumb_name;
@synthesize processed_image_name;
@synthesize processed_image_th;

@synthesize current_download_url;
@synthesize base_url;
@synthesize secure_base_url;

//Twitpic
@synthesize twitpicUpload;
@synthesize twitpicResponse;

//About UIAlertView
@synthesize about_title;
@synthesize about_messsage;
@synthesize about_cancel_button_title;

@synthesize image_expiration;

//API Key and Session ID
@synthesize api_key;
@synthesize session_id;
@synthesize num_retries;
@synthesize current_function;

//Singleton
static GlobalData * _sharedInstance;

- (id) init {
	
	if (self.selectedPoints == nil) {
		self.selectedPoints = [[NSMutableArray alloc] init];
		self.maxPoints = MAX_POINTS;
		self.default_radius = DEFAULT_RADIUS;
		self.nextPointName = 1;
		self.currentPointView = 0;
		self.ratio = 1;
	}
	
	if (self.thumbImages == nil) {
		self.thumbImages = [[NSMutableArray alloc] init];	
	}
	
	if (self.base_url == nil) {
		if (DEV) {
			self.base_url = [NSURL URLWithString:API_URL_DEV];
      self.secure_base_url = [NSURL URLWithString:SECURE_API_URL_DEV];
		} else {
			self.base_url = [NSURL URLWithString:API_URL];
      self.secure_base_url = [NSURL URLWithString:SECURE_API_URL];
		}
	}
	
	if (self.extension == nil) {
		self.extension = [NSString stringWithString:IMG_EXTENSION];
	}
	
	if (self.adaptiveResize == nil) {
		self.adaptiveResize = [NSString stringWithString:API_ADAPTIVE_RESIZE];
	}
	
	self.unrecoverableError = NO;
	
	self.adaptiveSizeX = ADAPTIVE_SIZE_X;
	self.adaptiveSizeY = ADAPTIVE_SIZE_Y;
	
	self.imagePositionX = IMAGE_POSITION_X;
	self.imagePositionY = IMAGE_POSITION_Y;
  
  self.api_key = API_KEY;
  self.current_function = nil;
  self.num_retries = 0;
  self.unrecoverableError = NO;
  self.queue_id = nil;


	return self;
}

- (void) resetData {
	[self.selectedPoints removeAllObjects];
	self.default_radius = DEFAULT_RADIUS;
	self.nextPointName = 1;
	self.currentPointView = 0;
	self.image = nil;
	[self.thumbImages removeAllObjects];
	self.queue_id = nil;
	self.queue_name = nil;
	self.queue_th = nil;
	self.original_name = nil;
	self.original_thumb_name = nil;
	self.processed_image_th = nil;
	self.processed_image_name = nil;
	self.current_download_url = nil;
	self.unrecoverableError = NO;	
  self.twitpicUpload = false;
  self.twitpicResponse = nil;
  self.image_expiration = nil;
  self.num_retries = 0;
  self.current_function = nil;
}

+ (GlobalData *) sharedInstance {
	if (!_sharedInstance) {
		_sharedInstance = [[GlobalData alloc] init];
	}
	
	return _sharedInstance;
}

- (void)dealloc {
	[selectedPoints release];
	[image release];
	[image_url release];
	[queue_id release];
	[queue_th release];
	[queue_name release];
	[original_name release];
	[original_thumb_name release];
	[thumbImages release];
	[processed_image_name release];
	[processed_image_th release];
	[current_download_url release];
	[base_url release];
	[adaptiveResize release];
  [image_expiration release];
	[super dealloc];
}

@end

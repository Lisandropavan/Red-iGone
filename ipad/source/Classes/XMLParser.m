//
//  XMLParser.m
//  Redigone


#import "XMLParser.h"
#import "GlobalData.h"

@implementation XMLParser
@synthesize currentElement;
@synthesize currentStringValue;

//Entrypoint for parsing XML data
- (void)parseXMLData:(NSData *)data {
	
	NSXMLParser * parser = [[NSXMLParser alloc] initWithData:data];
	[parser setDelegate:self];
	[parser parse];
	[parser release];
}

//Parse an open XML Element
- (void)parser:(NSXMLParser *)parser didStartElement:(NSString *)elementName namespaceURI:(NSString *)namespaceURI qualifiedName:(NSString *)qName attributes:(NSDictionary *)attributeDict {
  
  GlobalData *globalData = [GlobalData sharedInstance];
	
	if(elementName) {
		self.currentElement = [NSString stringWithString:elementName];
    
    if([elementName isEqualToString:@"err"]) {
      globalData.twitpicResponse = [[NSMutableString alloc] initWithString:[attributeDict valueForKey:@"msg"]];
    }    
	}
	
}

- (void)parser:(NSXMLParser *)parser foundCharacters:(NSString *)string {

	if(!self.currentStringValue) {
		self.currentStringValue = [[NSMutableString alloc] initWithCapacity:30];
	}
	
	[self.currentStringValue appendString:string];
}


- (void)parser:(NSXMLParser *)parser didEndElement:(NSString *)elementName namespaceURI:(NSString *)namespaceURI qualifiedName:(NSString *)qName {
	
	GlobalData *globalData = [GlobalData sharedInstance];
	
	//Ignore the root element
	if([elementName isEqualToString:@"RiG"]) {
		return;
	}

	//NSLog(@"current string value: %@", self.currentStringValue);
	if([elementName isEqualToString:@"queue_id"]) {
		globalData.queue_id = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
		
	} else if([elementName isEqualToString:@"queue_th"]) {
		globalData.queue_th = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
		
	} else if([elementName isEqualToString:@"queue_name"]) {
		globalData.queue_name = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
		
	} else if([elementName isEqualToString:@"original_name"]) {
		globalData.original_name = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
		
	} else if([elementName isEqualToString:@"original_thumb_name"]) {
		globalData.original_thumb_name = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
		
	} else if([elementName isEqualToString:@"processed_image"]) {
		globalData.processed_image_name = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
		globalData.queue_id = nil;
		
	} else if([elementName isEqualToString:@"processed_image_th"]) {
		globalData.processed_image_th = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
	
	} else if([elementName isEqualToString:@"error"]) {
		globalData.unrecoverableError = YES;
	}	else if([elementName isEqualToString:@"mediaurl"]) { //Twitpic
    globalData.twitpicUpload = true;
  } else if([elementName isEqualToString:@"err"]) {
    globalData.twitpicUpload = false;
    //globalData.twitpicResponse = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
  } else if([elementName isEqualToString:@"session_id"]) {
    globalData.session_id = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    
  } else if([elementName isEqualToString:@"error"]) {
    
    NSString * msg = [self.currentStringValue stringByTrimmingCharactersInSet:[NSCharacterSet whitespaceAndNewlineCharacterSet]];
    
    if ([msg isEqualToString: @"Invalid session_id"]) {
      globalData.session_id = nil;
    } else {
      globalData.unrecoverableError = YES;
    }
	}
	
	if(self.currentStringValue) {
		[self.currentStringValue release];
	}
	self.currentStringValue = nil;
}

- (void) dealloc {
	[currentStringValue release];
	[currentElement release];
	[super dealloc];
}

@end

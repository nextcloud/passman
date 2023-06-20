<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2016, Sander Brand (brantje@gmail.com)
 * @copyright Copyright (c) 2016, Marcos Zuriaga Miguel (wolfi@wolfi.es)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use \OCA\Passman\Db\File;

/**
 * @coversDefaultClass  \OCA\Passman\Db\File
 */
class FileTest extends \Test\TestCase {
	CONST TEST_DATA = [
		'id'		=> 21,
		'guid'		=> 'FC148F1A-AA67-489E-ABF5-4D7AA525F067',
		'user_id'	=> 'WolFi',
		'mimetype'	=> 'text/x-arduino',
		'filename'	=> 'eyJpdiI6ImVPcEY5WWRlT0FHcjdiekZ3V0RXSnciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiJtdG5CT2ZOL3hlRSIsImN0IjoialJFSHJ3S3JpK2FhaDJ5bUxUWnJRaWtKVVZIL2x4SSJ9',
		'size'		=> 4509,
		'created'	=> 1475853609,
		'file_data'	=> 'eyJpdiI6IjBlcnVNMTBMY3VpNWlUeitXc0JJdmciLCJ2IjoxLCJpdGVyIjoxMDAwLCJrcyI6MjU2LCJ0cyI6NjQsIm1vZGUiOiJjY20iLCJhZGF0YSI6IiIsImNpcGhlciI6ImFlcyIsInNhbHQiOiJtdG5CT2ZOL3hlRSIsImN0IjoiU21DRXBPSXRuNjhDeng0SEVYRjE0QnFuVTBmMkNLaGVDcVhoamw3UTcvTW9UL0MyUVdFZHRYS3dFSGNoTWRKbUVOdHJiaTltNWM3aWJXVzRvU0ZCdGVSemlpcXZtekJXdzNhSEtHNmRWK3duZHhqeVBibDZ6MWEyWEFZNDc4TXVoSzFmMG03bEp0ZXRHYUltL0ZyV1ZDVC9idG1GaUhucC9iVHNEeXVvdEdqVm5Qa1FMVTlKVTFuUGM1U0RvNGdrNlRodHV2Z2NMSkFEQWJ4SER0L3BIblBHSXRDV25LdjRqUnV3UWRNc2x2cTllenhjeDM5eXBwUWlhUG15dVVlcnEwZ3B2MENzdEhYQjcrWWVVRlJvOGpWM3l2dlVLT29wZjQwc3pFY0hVWXJ4QW1TRTJJNWdpZlJkczUzOGQxa0pDRjJyRkh3aFp4amFGR2plWXBrTUNPSzMrTytmdUFjSHE2QjlXWjU2SE5teExoZS9OczN2aytJZy8yWEt5bHFIcC9TU3k2YVNSckY1cFRSTi90dlJFN2RkOHRaeEVkVlZQZG5tdGJwRkxqKzlvRDlvazl3MTVPK2NoS25QVkhUUWtMb2JTVXRaNEh0SHdZVExQZFFoSUhYVHFTN3ExSHFPRWowamZTZVJJaEpoVFdVbWRPMGNxUC9nam50UWRHVzlCcGVhbS9MbG0yRWliRHFWMlZBNUhnS3JpYURrL1NaYmsrTnlkQVZsU1pNMXgzSlh1Qk5lZ2dvcWY5YkQ3NjY4VklyNFBQUWpqU2ZyYkpLQ3JyUUhZaEtyYXkwUUdJaURJOHVMU0tNZGVsVG1NWldKcVdGLzlNR3lXQk04b1RhWGdsMXpabGlCTHIvcjk4Y1J6UGplWGU4M2xOZWtOS1QwWEEySVJiYnQzdHAxK2NHdkxqblZJWTVrK28yRmY5ZEMyK2VXZ3NWTlJxbTZGZ1NGYW1CKzA5N2pZNjdIcmNQU3IrQlcrdG85aXZOYms4bXlBZ0VDN05kaHh0Y1VtTmE2T0VBSC9iMVBObHc2OThWK3U3QVgwWlhVQW1PQklOTVpzQ3JSTEtWTHo5MmEzK0NUMmVSeDd0Q2Fsb3lmakNyRFBDQlZtZTdOZVoyZHdHbWg5bUJZaVg0UFVCT3gycnFxMWpHUHkxRU5pTGdEL1dPUG5vRHgrQ21xenkzQ1Jlc28zVGlQNC82SklINmh2M3NMSUJjNXpUc29GSFJZZklRSUhtWkdvYk5lcXkwN1pKbGNUV01OenRtQ01acjFJWitpQ0RJb3VjWWZoNnVZcnV4WEd5YVRMYW1tSWJCOVNkL05PbFpOU2wweHlrTzVLMnV6U1JoRzIrWkoxSVk1L0ZLRUZpTU5xcjZNS2daWWdqTjlOalNsV1pOTnloWTRGUmlSYXNjaEtVeHdVR3dxMi9ZYjZhckZ2YlIwTUVVcUc2b0UrVWZBWVc1SnlPTzNXRUdlUTM4QjlhbklYT2pIOU5PYjcrdGgyanBaSThxR0pRVDZFMkc2Y2xhUUxFcXZrV3BhR2s1S2kyZTJzWlhhVFYxQmZCV3EzeHRrNWVFU1VpbXdLUS9WSHkwRXUrUDZLdU9lWHovSXlUQjQwczV4Q2JjWFIxODA0YkxqYWRvR0tTaGxxR2srdGEwT3V6TFVJbnBiTEs5SlEyREZPWjFseG9RK0lScXBzcVl1SHBYTUE2dGZHamVmb1JYODZEV2F4czk0UmxsWmVYRGlaQzFaQm9xVHEyQlVxcHdKZlhUb2E3WC83SEhIeDI4ZVdxRWswOXhuKzUwbzZqVG1HWjl4QkN0WHB1NjJ6SUlOMURrU0c2dzNhc3NVU0ROSVh5NkYwN29pQVRVZ2o0ZVpHZDVtN2RKMFRHTHhVUkpUU3I5YVhYeCtDMW9ZM3NZc2VaQ2RQTFhJTUcxT01EcmJid1NPZnFzMHo1Y3hsQWV1MUhiODQxRklkeU1pVnNzTndMYUJ4NTBlVHdUZHUvazF6b3A1aEFyL1g3Q1JndXBIbWl6V0RwdEdkNzlHWXNkSDE4VzZzczRlL2l2eGtKdFBlUi9hVmowUnNVM3AreWUxUFFLbzNhblZ3eGtqendmQndjQ3V5ZVY4dHJ5cXlkdlVmZnQvQVFrSCtlYVBHTVNnYWFLb1RhYlcydmFvVTh3WHBWejJtZVRvdi9CeCtBQkVFQVhxRzdRMkM3SUxEMGdRbnlQVDVSdm9MbUdHRXF1bDE2YWFEVXBNRU9PaGlSZ0xvNnVrbDV6Y0hlR2ZLVDd3NVMzYUFFaXZWeElTR3N2REh3eGVzRm4zb0ZwUWhMeFg2R2gveTRYQ201cmtNRHRpMWJQc1gvUWc3dUxEQk9yOWdBaGNzMmE0MHpvbjBHUkVSVVR3aDB2a1d3ajhKUDFmREdyRDNMdXVaMzI5MTlLT1NGRWRZckJwcFM2YmRrdU5OVDBPclJmcmJjNTFhdGltcUUwZUtUSWNFTTJYcTZYQ2NiUW41eklDS2NqdEE5aDRUWHdiOFRmdnY1OUJqYmZvN2hRN3RYYUlHTzlpNjdacC9NSjdzZm93bVZOMHEvY2lJSVBKejdiMld2Qy9Dc2s0OWZ1TVl1bGpOZFlqWHVrTHlyS0FqZWVUZXRRUm9lSVdyV2YzVUIvdlBtczVMdkV1ZEJidXhwdDNIRHhLbmxsbjhBc25mZkR4Vi9QSUsxN1MyNDJhcTZDR1dtTW9mcVRzbmJlbSt2WXlVNEN4TnFuNFNEcWNpeW1CZ3dTZWsvY1BxSlFZdVhwcFpEUndIT0NxMTg4WlQ1bWU4M3dmZDcvSjMxMlZFNjJTSTBaaS9oNjJpU213TDIwcjc3R0xBSktncFV1TENaay9XUDZ0TFQwM1F2cnlpT3dzcks1OFFjOXcwdlFBdTI1cUxZZXZNZkVqajBjMllWK0VDLzZJd0hmYmt3MDdxb1BOcThMVEhtbVMySmUya2IwbjlMbTdic3psV2RUOW9TTUQxNGlKQzFERG8wK3gvd1VGSjdTRCt0aWVQcElhVmVvdFZHWUJneDR0MHJ1dXV6d3BUdnk3eGFnaXNuem5EV1NvaE1hVUp0SzBjOTM3Q2RlQUp5UUM4NDJCMDIrdjJzSjhnRjNVNXpZUDdMak5JRlZVQ2xRYjBiVXdhc2ZqaTdzeUxmM3Fwc0RsMlRhWHhUL2pkcjhDSXRFcDF4OVg2OTFKaVJaSE5UN0E4M2hzSk5SSlc1MVFQYkx6NXZzd1pSU05OSGhaeDkwR3lhSW51S0ZvS1A2TmVnU1hFdHBvQytQRXlJUG9iM1k3cmJVVnpsSldPLy9SS29KTkJSTWUzQXluTVo5VFc0KzZFWUh5VHFjc0JwRHN2V2NhZE5SVlhKK1cxaHp5clZBSVl4RGJOMHBndXY4a0lvWmNYakN2dzBsRWpLby9iUk1ONlZpeG9URzQvZ08vY2RQcjVQT2VDYWNlTXlYVFZhYm1GVGc2UVBmSXNsY2xSckhlWTh5MGZhdmoydFo5ZmkrWmtxNHBub2FlenI0RjFzOWcwUmwxWTNBU3AweExkUVVEZWFwdnc3YXYwQlJyWjZEN083Wm9lajFtbkdFeEJLMUIvMmMxUHErYVhpRnZ6azUzejBibDUydGh0ckZXV3NNN1YxY1NjV1RkNU1RZlRaMFRBODJqRjJkR1B0eDdTTDJpbFVSN243bE5ucXdFZWpNSzdlUno1QVNwRXZ0QlozN0NCQkxSS0tHZ01JZUMvcFNFM0tvZnhKTUtuckFsV2JUNHJQZUsxL0VvSkNFeStRMHNvdXM0K0hmL3dPd2hOc3NwNFY1QkdFTHgvYXRoUTZNcGJRbE5vZkRwcFl6WGM1WHdwRlE3QkNGU1ZUTngwcTF3bkRFMDd1UFlBQTZnTlJzODFqZXZXajZ5NDVRTTRrbzdZRDRMRVNjTCtidzI2aStuS09UMFRsbGJGUVhyZFQ2MWxkdGlKK1hYcVJYbTVPVHkwMHIwQ1E4aE5mT0dWRTdyVVBnYThoQi9uK3NwczJ0MHd6ZXFDbVlzWDJUUTkzZ2RQc3NqbHJia0VvZTAzUXRQampUUmY4c0Fsd1FVWllEbmJyMTJLeUxWS0R0NlRZQWErbkZmeXhBTGpia3puRXZRaXFhR253Tkw3RDBiNXhDTmtVdWs2c3ovVnBQSkdyWDRaMHZCQjgzWEhYMGE5Q01zaUpBak56bk50NVB0eVpRRjN4OXBiRlBKZWJLc3RhaUtYRzlDSzJqWi84TVErQUgrcHlrYzB6RmZEc2FwQlJVR3Yvc21sWnJ4c2czTGVWMGdHRDF0c01ZNnRVREN6Mk9lRS93ZzN3Wk0vQXg0QnNsenNJTVMxOGlzRHhYRVN0WU50a2JkV1FRWlc4MVNsYkdodGU4aCtvbmZFNGZiSGZaL3lySDFNQ1lCQzZRZkJWbnYxZDBpQ0VZYXBGRk5vQnhwL3NhQ2NzdVlDSTRxbklKUDJkc1BhSnBwbzRMMTd3cm9IcitueTFlQXNOdU53VjE4S28rMy9DdmI1cGs0MVhSNlFaV1hrVlBibFhFVGNyZGEraE9OUy84OHUzWXBlN3g4SElzTS9FbFZncGlYRUVTUk5YVGFTK0FOOXVYMzRIWDB6QVBQQjN3M2xRV09HTUtEdUs5Qi9QdkU2UGJZYnR3cklFdUFtZ2Jic1VORXJmVEZWVkxZSVpBeDZtNWZwZGd6NTR3cEMzWVcralhnT0tqeUZkc3hPb3h3SDdEYVMrc01EcUNmVi8rTlMwK0JCR3B2ME5nSGN6cGJLRldCVlFHaFhFU1JSZmIwOW0wcnplRWt1dGZPOExsTkdiRnozZGdYZ3ltNjBtcENNQjRhNW9VZW9CYXNhQm0rOFkzYjI1bTQ0Q2E2UmlYK0M5YVNDVHZmVDF3VUhtcGVKVExLbXBxU1JuZWkrOTNYSXlyWlRSTHI2M1hNMUNtOG9wdEZNNHJSRzh2Mm4wQlprL1diM2ZmNkVZYXUxUGFwWS8vRUt2dktQa2lua0hUN3pvYko0bTdxaDdNYXphSVhqRW5tYUkvaVcyY2ZMNS9ZbXQxM1ZNc2JWcm5MZGk5WktJWjJUcGxzc1YyZ3JUaXFhYW5GY2Yva1UvWEkwb3JBWHJ0MUtHTnpwMGR3eXlzNmphL3dubllUK3RYWXRwdE9OSk9BQit4R3YvMFJaYkI0Si8yVmxwYVRJK1AvQkRIUSsxSFhHV1E1bmkxQTBMY2FnbEl2NzUwL3hucDd2aDgzOU1KV1M5M0VJUllJK3RHWm1aM25QZFh6OG1Zbjh3VkhsbVJ6YTV6QnQ0ZzBlT3p1ak1kMnZaaXh6Wk9hekRGTHpXWE5PVzAwbWZGNEl4Q05PNGNNeVNYWk9pdnh1TTFPT3JBUGNqeVlyOGZGekVZdEtsRmhCTndFV1Q0Vzc1T1FTRGpTSkFPcnZ0dzdSSnlseU54eHpVRjZodk8zR0VoKzlJakhOT28vYlZPRTJQZ1dVY3BLbFpJbDNCbXhqcXIyUDlkQXNnWUZXVDg2ZC9rVlFPdkNGNGQycFFQMWRXS20veExadEMrQzN1Mm1sakZsYnVXdEh1UEhSSjk0UTJYL3dBWGZxdmllamZOLys2VUZ6cGtlYU1RRG5DVjRGR3RWc1VvY1BCY1lUbW1NMHJxMnAxK3JtYzdrTjBvL3JDNGRUNWVNNWp6bzlGSmdLQXdnQjM5dG9PUWY5dUViZTVPeDVyei9acEk0UzBoM0k1bisxSFVsWDlybEFTWTZlSVlJQlQvOFZaN0JrdGtvRnZBRnM5ZU0rRDJyL3ZsZnMyemtnc2h6ZzJYT3RJeWx5ZHN4TUFEclBBanVZMnBvd1hwZCtYOTRadUo5S2Fkd1ZsejA3Q3pHaTIwMU5LTi9LRTNLcHZBTDI5NWx4NTRkb2hxNFFvYkxhb1NFamw0S0w2MmpPYllvbTJOd1N2ajIzYUhucXB0MDVpSVNoUERBOGZLOTVaclArRXlScElmSENJdFM4Uk5jdXB3UDVFWXJEbGxqdkNzZU5CcDBIdTJUM1V0NzJrYVJHa3BTd0dORElWRVhZOWJTdXIyQURjVmxuTWdZZW9rL1puYUQ3K0NjVUc2NEdCRzBKZS96OHk1QjZ5eFNUN2JvSmUxeUFoQ2hyd2kwSG9CKzRQTVJUVzNNeVJWekVGbUh4QjF6cjlmS0tkMUo1OW1ZZzFXR1k3ZW9tNmpjYXU4WU5RQUluL2tNQTY4MGxHdDdDTGtIRXd1WkZ6K0Q4cTIzRXIvK3pMWkFyRHBPMW5VK09ScU5RSlExaVdsdGhyazlpa0lTaUR3M25sdGplUklrMWlVOGErMDQ5UnZPMGErL3dTRVNTcXF0WkRhc2poRFd5NkY3dDliMnNzVmFwYXM3VWprTWw0Z2daeFJEeGFtakVPSi9GNUozcHloNnFEMTVBNGJMMmZuTFZZZzkxaDFTbmJzOEdVMTM4QmpOVXIrM3haZ3NxWVptVTN0R1JJOEY0WnpMcTR0VTgzZEtTdFFBV05uSHkxdTkrOTNvOGlUYlJmZE13bVFYZUpDQlFhd0dMay90MzBsNFlQTGNBZXpNSXQyQllab1pFdzZJQkdWcXV1TTFob29ZcmhubUxHamhzR01WZm5Oc2lTNHlGM2dQK0M1eVl3MjdsaG9lb2R5cnpiangzK3l0UzF1em9JMEl6NTVZeG5QVmlMVUREWWhEL3B0UERscHJzUVp6UE1ycWJ6MjJwOWhNRlkzcGE3OHhIRXJ5K05RQlRmUncwZys4cGk3V0xXRStoNU02V3psSWNMcEEzSXIrRy8zUFdqWGpvRWwxZi9vbmZ3WEdzQUZwVjZ3UkZ2NlFoZi9mMFZINWlLRkRrWENRbi9nenlxZmRkSko3Q2M5K2lHSzZhSGF3MXRuODgzdmVNVEhGWkpDa3FTSTl4YjJzTHpLRWYwSFg0WXJnbStyL2c4WWFGUUd0QUxYZVZUM0k0em9RZ0VyWFh0OUlCeGQ5TjVsOHBVcTJtVEF2MnAwL0tVWXFNUTFueUI1MmtIa01xZlZGTjR1amJyYnhjWkUzRXQrdE5uanUzRzAwbUhkWXJSZ2xIeGdPZm1nMXZMOFRjdmV4UG5aaDROUVArTEtQZFR5TTl3a1RHME15TUNERm9mZ28wN05DM0kxM1gxQmdSYit1Q2ZxN1U0RGVpVFJoVU4wMWNMNy94UkFSN29NQW8vYy9xZC8rTHlXNWJPdnNpU1UwK01vWXFpRVc1S3AyMXFNaUhNNzRVcDRLS1E0WlFFZHBQb016c1BXNVNGL1haTWdIMi8yVjFsOGJacmxFSDJ4SGRZUXdqUDVEQktMdXVOYzdBV3ZIM2s5VVdUZ0tZL2lORmNNWVhRQng1YzB2K2lOWDBrN0lrVXp1RktxYVhlM2xuZTFESzl2OE1YdWNrZytFY1ZWdXcrOGhJVmRRLzBLWnVXYTBqeDU4L09XMW9KMWxSN3l4dSt0TWptMVFLTExvWkVzUUtLZXVBdE1kd05ENEgxMjZSU3ZyNStlSmVmRUN4ekQrbkJRalowRlNQenRtUGR5Wm1MdE1CREJlYWdXOENkWVhheTNYbUxlbFlDdlVrWFg0ZGFCQXZZNmlwT0FBOVlkYm52R2pXaUhQTEl2RU14T0VqTDRCM245Zmh5L0w2Mk9xQVdBaWViU3FVR1llMkQrWmNQTkEvZ01GbmpkSTk4QnVBditZeTYzQklSVHdjZWErU0Y5NjUyOXVXRmNjWjJIVFBxSkV2T0xLVlZIeW41MWJPRlMwNzlEUnJIT1BRLzk5WEJ0WGFRQ1U1cmlXRFFVNDhrVERKcGYxREZZZnJ5cDNPdXJKYVEzV3BwOFJtRVNkR28zdDBJNjMrYzQ3SnFCMnAzcm5HSUVFTERmckZMNHBLRTRoZm1RKzhScjFkeGhRZUxuRFdRSWtzckNJK2NTRXR2bzhtQ1ZMMFlIV0tEVmYvY1gxZzBVdk9MVG1VWk9VNWRsbm1sTjdMcEowc0pPT3dFQkw4VWY1a05YK3FSWnRyZm45TGhmTjBnVXZTdU1PMUY3WWp4MWllQUxQSURjU2c5RFdLL0dsMVl6V09oUm96dWdTYlJvcmVUT00xSHBRY1FZMHRpNEdxd0ZXZC93OVR1MHVodVdlSHZTcXY3Vmw1SCtuNkg4eW9RMlhveEhIRXAyUHUvaUNYUlZ1RUloMHlKYVV5TlFUNTFqMUJlTjlPZUMrRERvdnFkRnhqVWxLSjhENDkxR0hWUkZKbExCb3JJTFNGc1BQQXdZT0RJM2RzMDBJQUFIakE1UldlMXoxUU5lbGhWVjVLSDNMUkxiSWlsb3hrb0o5OGcyckI5VFR0ZC9aRnBFeWgwcTJpcFhyd0xpQTVhYkErdXZMRTJQRkVuSkxmOHFYVUNhWW5GLy9Pbko4SHU4dytiMFMxRXEwQUdrWEF3OGI5ZXAvU1BEc1ZZeXdQbGprdGdvN0kyNFF4VkNqSTJkSDBVRDBoUHhBcGpEVGMwYnpiNEhwZmlCMkJETk8vKzZFUTdkQ0tnRHJTa0dXTWQwc3NCb1IyWFNTUFByc09pdWdmV2hCYWJBMVVrbVdXcUlwUzZIeXhZSUFvdVVqckhPVmxuMlRGTDF6a1YzNmlFL1RMRDhRZzNRQUdXdllETWljVnY0RXNqcXZCam5xZnlubU9BUnIvVGhNNmhLUFNZZ210UGJNMkNjOTVHaDJxTlkrYjNFekxqbDJ4R2JCbkRwZTJENDgzOEZ0NEVKU1BnenhiTmsvcHd6VVZwRHplM1VGSjNQcVBlc2dQSE90SW56VFM3MmJDZEhIZXZ2UzhXWWtYbXJYOUs2eEpuRXhQcUxSUmN6MzBBMWw1Y29iNTdYdDJtL1FLVC9zOEIzenlUNGJnZjl4Rm0vUDJhZkJ3bFZzY1c1cWpLWXorY0tNbWJCUVd5Ny9DWGpiYXNRRmRpbnRQcGc0Qk1VNXJUZ0swTlBTQWVrRU1lcjNPK29LdW9TajFkMVdqbURVZE1WQ3RpZmhDNjc2aUYyUnRkZE9tbms2SlRSQ05EczBWM3dpWnl2bThZV0tKRWZ4Q1IxanpOY2JteGFQNGh1T0l2SzRMZmozRjJWVUZhSHFBQUJmTnFvVjdKUmoyZUd4U2txMGZrZlE4eVF5L2FOanRpMU1vQVNST2VEYmNVd1ZRd3ljSWxtUVVEbk1PQmVTT3pXdjF0aUFPdWdqVVJkN1J6UlJ0VFNRWWdPV1hzbGU4dzZBT0VOcndaRy9aSWp3SnN6L2tpV0EyTjZFaUZ2UGtuRStpOWxZR0IySTFUbC9lcm1mWjllbXdIK1M3YlR3eUprdHlCY0p6SHBiS282OVFEMnVMZkRMU1VBZVNtVDVRak4xYksxRXgvT2FpLzIwbXJEYkw3Tk0xWXUvV1VSVDB2L2dEZUIzOFlkaVFjcmhOZm51cWZveTQ0OGlMMzJBQk9FbCtGcmgvMHU0ZG42bVRZNHZqNDduUGdIcC9aME96MFVBUkM5Q2pKY0FNYkppbjNKN05rZE80SlhtZ0ZCZ2Q3b1UwWUJSRGMveXZVVUQrZTdmbnJKODlvT2NpRUcydVQ2bElPT1hYQ3BCaEJISmJZOFY5anBzOHJmSzFuNzhpWHpNNlNLV3NUeXhMcWpLOEtWYTY0UDJQdTVaSi94dVNHVVpuaXZxTG8wdkZLNUF6QWt3emR4NVJzY2x5ZnA4MUlLUHJTelIxRzBHbFpLdkEyR0kyKzQ1VXpRdVREd0thZm1BbUQydHpJTlh4Vi96cStTYmtsdytRV2pxNTlMbHlHLzhDOUhweDRBNVVTNDJkSnI5cENPUmFvRnBLbWFkZk9PNldVK1BqWGJGV05QRXdOMXJyb1FmWGNqaHY1UGpVV0lpbDZWT05nV1B2bzZ6VzVrQnhsSTFtVnpPN1REdFI4ME4zdVhBc1JQQXJwRnB5QldRbmJEdHlMWVRPSG5WWVMweDBlMHAya1VZQlkxTWhIQkFEdW1NT01zTlo5NytXRktYRjNlTkEyQWhpNm10OFRBc29TRURYSHo4dFljUUVqOXRKTDV2WEFMUm1HaEFqbEpQdXh1VHJSVEVjWnB5dHBTUmZ2Qm1aSHIvWldnZ1REKzYxNTh1SGJHMlhaTUJ4eEgwNWxkTzFsOWpFSnBYUnIrLzFqcVp6aHN1MW8yT0tvYnlzekZiaHg5RkdZVXltc295Z0ZXd2ZoZGNiUmRHd3IwNVFSa3VpMHo0OHduYmJ1bE5jTThhOW5QcmZ0d2xMOU01cGFJNUhTMzZ1VTJGVE04RjQ3b0hFU29RdUc5TXI5RWRaZnJUWWM4M1Z1aFNrdmkxOFhibnpxTDRnbXVreEFuUVl0L3hFT2ora1E0cU5ZVmE0dFZCQnFUV1QxeTRxU2JieDlvRm5IZG4rdU93czZ3ZmhBclhvRHpHMDIzWTY0T1dFdUNBTmJ4WjBRT0lTdTYzZGpPalBPczBhbDVzdXVSSWQ2dTlublp4dUE2MEJSLzBmdWd6R2N2UG5qZi8yNkJvc0lCMUhWY1RMc3pDR1R6Q2hDV1RQTk92TU03Zk42UGczN2l2dnNGdFREelVwcm5NQ1VpY0JYU01LbjBwTjBHbmZvU2I5cEdodDhyT2RBREhuMk9TbDVhK0tiMUhKa3VDZjFzcTBRcHhlTXRJb1VhcnZXVWduRkJwcVZaSlQ1Y1AwekljNjkrb3pyYmJTMkJKS1ZSWSsrbXRMSnJ2dXhqTE12MjgzRzQifQ==',
	];

	/**
	 * @var File
	 */
	protected $file;

	/**
	 * @after
	 */
	public function setUp(): void
    {
		$this->file = File::fromRow(self::TEST_DATA);
	}

	/**
	 * @covers ::getter
	 * @covers ::__construct
	 * @covers ::fromRow
	 */
	public function testGetters() {
		$this->assertEquals(self::TEST_DATA['id'], $this->file->getId());
		$this->assertEquals(self::TEST_DATA['guid'], $this->file->getGuid());
		$this->assertEquals(self::TEST_DATA['user_id'], $this->file->getUserId());
		$this->assertEquals(self::TEST_DATA['mimetype'], $this->file->getMimetype());
		$this->assertEquals(self::TEST_DATA['filename'], $this->file->getFilename());
		$this->assertEquals(self::TEST_DATA['size'], $this->file->getSize());
		$this->assertEquals(self::TEST_DATA['created'], $this->file->getCreated());
		$this->assertEquals(self::TEST_DATA['file_data'], $this->file->getFileData());
	}

	/**
	 * @covers ::setter
	 */
	public function testSetters() {
		/**
		 * Only testing one setter since if it works all setters should work because php magic.
		 * please, if you override a setter implement it here.
		 */
		$this->file->setMimetype('text/json');
		$this->assertEquals('text/json', $this->file->getMimetype());
	}

	/**
	 * @covers ::jsonSerialize
	 */
	public function testJsonSerialize(){
		$expected_result = [
			'file_id' => self::TEST_DATA['id'],
			'filename' => self::TEST_DATA['filename'],
			'guid' => self::TEST_DATA['guid'],
			'size' => self::TEST_DATA['size'],
			'file_data' => self::TEST_DATA['file_data'],
			'created' => self::TEST_DATA['created'],
			'mimetype' => self::TEST_DATA['mimetype'],
		];

		$actual_data = $this->file->jsonSerialize();

		$this->assertEquals($expected_result, $actual_data);
	}
}

/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Matt Burt 2024
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 *
 * @file sesman_restart.h
 * @brief Sesman restart declarations
 * @author Matt Burt
 *
 */


#ifndef SESMAN_RESTART_H
#define SESMAN_RESTART_H

/**
 * Discover sessions from a previous sesman run
 * @return 0 for success
 *
 * Errors are logged
 */
int
sesman_restart_discover_sessions(void);

#endif // SESMAN_RESTART_H
